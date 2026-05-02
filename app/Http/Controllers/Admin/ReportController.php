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
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->toDateString()))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()->toDateString()))->endOfDay();

        $paymentsQuery = Payment::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$startDate, $endDate]);

        $bookingQuery = Booking::query()
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('booking_status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value]);

        $monthlyRevenue = Payment::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereNotNull('paid_at')
            ->get()
            ->groupBy(fn ($payment) => $payment->paid_at?->format('Y-m'))
            ->map(fn ($items, $month) => [
                'month' => $month,
                'total' => $items->sum('amount'),
            ])
            ->values();

        $serviceBreakdown = Booking::query()
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('service_name, COUNT(*) as total')
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get();

        return view('admin.reports.index', [
            'filters' => [
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
            'monthlyRevenue' => $monthlyRevenue,
            'serviceBreakdown' => $serviceBreakdown,
        ]);
    }
}
