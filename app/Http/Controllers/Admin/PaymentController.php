<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with(['booking.patient', 'booking.doctor'])
            ->orderByDesc('CreatedDate');

        if ($request->filled('status')) {
            $query->where('payment_status', $request->input('status'));
        }

        return view('admin.payments.index', [
            'payments' => $query->paginate(12)->withQueryString(),
            'stats' => [
                'total'   => Payment::count(),
                'pending' => Payment::where('payment_status', PaymentStatus::Pending->value)->count(),
                'paid'    => Payment::where('payment_status', PaymentStatus::Paid->value)->count(),
                'revenue' => Payment::where('payment_status', PaymentStatus::Paid->value)->sum('amount'),
            ],
        ]);
    }
}
