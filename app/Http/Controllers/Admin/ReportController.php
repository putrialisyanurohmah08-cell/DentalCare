<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filterType = $request->input('filter_type', 'daily');
        $month = $request->input('month', now()->format('Y-m'));

        if ($filterType === 'monthly') {
            // If month is empty or invalid, default to current month
            if (empty($month) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
                $month = now()->format('Y-m');
            }
            $monthDate = Carbon::parse($month . '-01');
            $startDate = $monthDate->copy()->startOfMonth()->startOfDay();
            $endDate = $monthDate->copy()->endOfMonth()->endOfDay();
        } else {
            $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->toDateString()))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()->toDateString()))->endOfDay();
        }

        $paymentsQuery = Payment::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$startDate, $endDate]);

        $bookingQuery = Booking::query()
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('booking_status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value]);

        // Determine if chart should be daily (selected a month or range <= 31 days) or monthly
        $diffInDays = $startDate->diffInDays($endDate);
        $chartType = ($filterType === 'monthly' || $diffInDays <= 31) ? 'daily' : 'monthly';

        if ($chartType === 'daily') {
            // Group payments by date
            $payments = Payment::query()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->get()
                ->groupBy(fn ($payment) => $payment->paid_at?->toDateString());

            $chartData = [];
            $tempDate = $startDate->copy();
            while ($tempDate->lte($endDate)) {
                $dateStr = $tempDate->toDateString();
                $total = isset($payments[$dateStr]) ? $payments[$dateStr]->sum('amount') : 0;
                $chartData[] = [
                    'label' => $tempDate->translatedFormat('d M'),
                    'total' => $total,
                ];
                $tempDate->addDay();
            }
        } else {
            // Group payments by month
            $payments = Payment::query()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->get()
                ->groupBy(fn ($payment) => $payment->paid_at?->format('Y-m'));

            $chartData = [];
            $tempDate = $startDate->copy()->startOfMonth();
            while ($tempDate->lte($endDate)) {
                $monthStr = $tempDate->format('Y-m');
                $total = isset($payments[$monthStr]) ? $payments[$monthStr]->sum('amount') : 0;
                $chartData[] = [
                    'label' => $tempDate->translatedFormat('F Y'),
                    'total' => $total,
                ];
                $tempDate->addMonth();
            }
        }

        $serviceBreakdown = Booking::query()
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('service_name, COUNT(*) as total')
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get();

        return view('admin.reports.index', [
            'filters' => [
                'filter_type' => $filterType,
                'month' => $month,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'stats' => [
                'revenue' => $paymentsQuery->sum('amount'),
                'visits' => $bookingQuery->count(),
                'paid_transactions' => $paymentsQuery->count(),
                'completed_visits' => Booking::query()
                    ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where('booking_status', BookingStatus::Completed->value)
                    ->count(),
            ],
            'chartData' => $chartData,
            'chartType' => $chartType,
            'serviceBreakdown' => $serviceBreakdown,
        ]);
    }
}
