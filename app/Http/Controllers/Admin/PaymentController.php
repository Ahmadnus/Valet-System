<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['request.user', 'request.car']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($method = $request->string('method')->toString()) {
            $query->where('payment_method', $method);
        }

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('request.user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        $allPayments = Payment::query();
        $stats = [
            'total_revenue' => (clone $allPayments)->where('status', 'completed')->get()->sum(fn ($p) => (float) $p->amount),
            'revenue_today' => (clone $allPayments)->where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->get()->sum(fn ($p) => (float) $p->amount),
            'pending' => (clone $allPayments)->where('status', 'pending')->count(),
            'failed' => (clone $allPayments)->where('status', 'failed')->count(),
        ];

        return view('admin.payments.index', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'method', 'search']),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,failed'],
        ]);

        $payment = Payment::findOrFail($id);
        $payment->update(['status' => $validated['status']]);

        Log::info("Admin set payment {$id} status to {$validated['status']}.");

        return back()->with('status', 'Payment status updated.');
    }
}
