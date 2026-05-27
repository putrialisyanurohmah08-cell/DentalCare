<?php

namespace App\Http\Controllers\Patient;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\PaymentPaidNotification;
use App\Services\BookingService;
use App\Services\MidtransService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BookingController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        return redirect()->to(route('home', $request->query(), false).'#booking-section');
    }

    public function store(Request $request, BookingService $bookingService): RedirectResponse
    {
        abort_unless($request->user()?->isPatient(), 403);

        $validated = $request->validate([
            'doctor_id' => ['required', Rule::exists('users', 'id')],
            'service_id' => ['required', Rule::exists('services', 'id')],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $bookingService->createBooking($request->user(), $validated);

        try {
            $request->user()->notify(new BookingCreatedNotification($booking));
        } catch (Throwable $exception) {
            Log::warning('Booking notification failed after reservation was created.', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'user_id' => $request->user()->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        if (filled($booking->payment?->redirect_url)) {
            return redirect()->away($booking->payment->redirect_url);
        }

        return redirect()
            ->route('history.index')
            ->with('success', 'Reservasi berhasil dibuat. Tautan pembayaran belum tersedia, silakan lanjutkan pembayaran dari riwayat reservasi.');
    }

    public function history(Request $request): View
    {
        return view('patient.history.index', [
            'bookings' => Booking::query()
                ->where('patient_id', $request->user()->id)
                ->with(['doctor.doctorProfile', 'service', 'payment', 'medicalNote'])
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->paginate(10),
            'pendingStatus' => BookingStatus::PendingPayment,
        ]);
    }

    public function checkPayment(Request $request, Booking $booking, MidtransService $midtransService): RedirectResponse
    {
        abort_unless($booking->patient_id === $request->user()->id, 403);

        $payment = $booking->payment;

        if (! $payment) {
            return redirect()->route('history.index')->with('error', 'Data pembayaran tidak ditemukan.');
        }

        if ($payment->payment_status === PaymentStatus::Paid) {
            return redirect()->route('history.index')->with('status', 'Pembayaran sudah lunas.');
        }

        if (! $midtransService->isConfigured()) {
            return redirect()->route('history.index')->with('error', 'Midtrans belum dikonfigurasi. Hubungi administrator.');
        }

        try {
            $statusPayload = $midtransService->fetchStatus($payment->order_id);

            if (empty($statusPayload)) {
                return redirect()->route('history.index')->with('status', 'Belum ada data transaksi dari Midtrans.');
            }

            $status = $midtransService->mapStatus($statusPayload);

            $payment->update([
                'payment_method' => $midtransService->extractPaymentMethod($statusPayload),
                'payment_type'   => $statusPayload['payment_type'] ?? $payment->payment_type,
                'payment_status' => $status,
                'transaction_id' => $statusPayload['transaction_id'] ?? $payment->transaction_id,
                'raw_response'   => $statusPayload,
                'paid_at'        => $status === PaymentStatus::Paid ? now() : $payment->paid_at,
            ]);

            $rawStatus = $statusPayload['transaction_status'] ?? null;
            $bookingStatus = match (true) {
                $status === PaymentStatus::Paid => BookingStatus::Confirmed,
                in_array($rawStatus, ['expire', 'cancel', 'deny']) => BookingStatus::Cancelled,
                default => BookingStatus::PendingPayment,
            };

            $booking->update(['booking_status' => $bookingStatus]);

            if ($status === PaymentStatus::Paid) {
                try {
                    $booking->patient->notify(new PaymentPaidNotification($payment->fresh('booking')));
                } catch (Throwable) {
                    // notification failure is non-critical
                }

                return redirect()->route('history.index')->with('success', 'Pembayaran berhasil dikonfirmasi! Reservasi Anda telah terkonfirmasi.');
            }

            if (in_array($rawStatus, ['expire', 'cancel', 'deny'])) {
                return redirect()->route('history.index')->with('error', 'Pembayaran telah gagal atau kedaluwarsa. Silakan buat reservasi baru.');
            }

            return redirect()->route('history.index')->with('status', 'Status pembayaran masih pending. Silakan selesaikan pembayaran dan cek kembali.');
        } catch (Throwable $e) {
            Log::error('Manual payment status check failed.', [
                'booking_id' => $booking->id,
                'order_id'   => $payment->order_id,
                'exception'  => $e->getMessage(),
            ]);

            return redirect()->route('history.index')->with('error', 'Gagal mengecek status pembayaran. Coba lagi nanti.');
        }
    }

    public function paymentFinish(Request $request, MidtransService $midtransService): RedirectResponse
    {
        $orderId = $request->query('order_id');

        if (! $orderId) {
            return redirect()->route('history.index')->with('error', 'Order ID tidak valid.');
        }

        $payment = \App\Models\Payment::where('order_id', $orderId)
            ->whereHas('booking', function ($q) use ($request) {
                $q->where('patient_id', $request->user()->id);
            })
            ->first();

        if (! $payment) {
            return redirect()->route('history.index')->with('error', 'Data transaksi tidak ditemukan.');
        }

        if ($midtransService->isConfigured()) {
            try {
                $statusPayload = $midtransService->fetchStatus($payment->order_id);

                if (! empty($statusPayload)) {
                    $status = $midtransService->mapStatus($statusPayload);

                    $payment->update([
                        'payment_method' => $midtransService->extractPaymentMethod($statusPayload),
                        'payment_type'   => $statusPayload['payment_type'] ?? $payment->payment_type,
                        'payment_status' => $status,
                        'transaction_id' => $statusPayload['transaction_id'] ?? $payment->transaction_id,
                        'raw_response'   => $statusPayload,
                        'paid_at'        => $status === PaymentStatus::Paid ? now() : $payment->paid_at,
                    ]);

                    $rawStatus = $statusPayload['transaction_status'] ?? null;
                    $bookingStatus = match (true) {
                        $status === PaymentStatus::Paid => BookingStatus::Confirmed,
                        in_array($rawStatus, ['expire', 'cancel', 'deny']) => BookingStatus::Cancelled,
                        default => BookingStatus::PendingPayment,
                    };

                    $payment->booking->update(['booking_status' => $bookingStatus]);

                    if ($status === PaymentStatus::Paid) {
                        try {
                            $payment->booking->patient->notify(new PaymentPaidNotification($payment->fresh('booking')));
                        } catch (Throwable) {
                            // Non-critical notification error
                        }
                        return redirect()->route('history.index')->with('success', 'Pembayaran berhasil dikonfirmasi! Reservasi Anda telah terkonfirmasi.');
                    }
                }
            } catch (Throwable $e) {
                Log::error('Status check on payment finish failed.', [
                    'order_id' => $orderId,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('history.index')->with('status', 'Reservasi Anda sedang diproses. Mohon tunggu beberapa saat atau silakan cek status pembayaran.');
    }

    public function paymentUnfinish(Request $request): RedirectResponse
    {
        return redirect()->route('history.index')->with('status', 'Pembayaran belum diselesaikan. Anda dapat melanjutkannya kapan saja.');
    }

    public function paymentError(Request $request): RedirectResponse
    {
        return redirect()->route('history.index')->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
    }

    public function invoice(Request $request, Booking $booking)
    {
        abort_unless(
            $request->user()->isAdmin() || $booking->patient_id === $request->user()->id,
            403
        );

        $booking->loadMissing(['doctor.doctorProfile', 'patient', 'payment']);

        abort_unless(
            $booking->payment !== null
            && $booking->payment->payment_status === PaymentStatus::Paid,
            404
        );

        return Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
        ])->setPaper('a4')->download('invoice-'.$booking->booking_code.'.pdf');
    }

    public function medicalRecord(Request $request, Booking $booking)
    {
        abort_unless(
            $request->user()->isAdmin()
            || $booking->patient_id === $request->user()->id
            || $booking->doctor_id === $request->user()->id,
            403
        );

        $booking->loadMissing(['doctor.doctorProfile', 'patient', 'medicalNote']);

        abort_unless($booking->medicalNote !== null, 404);

        return Pdf::loadView('pdf.medical-record', [
            'booking' => $booking,
            'medicalNote' => $booking->medicalNote,
        ])->setPaper('a4')->download('resume-medis-'.$booking->booking_code.'.pdf');
    }
}
