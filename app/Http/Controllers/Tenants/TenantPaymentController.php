<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantPayment;
use App\Models\Tenants\NetworkUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\TenantPaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class TenantPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = TenantPayment::query()->with('user');

        // 🔍 Search filter
        if ($request->search) {
            $query->whereHas('user', fn($q) =>
                $q->where('username', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
            )->orWhere('phone', 'like', "%{$request->search}%");
        }

        // ✅ Disbursement filter
        if ($request->disbursement) {
            if ($request->disbursement === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('disbursement_type')
                      ->orWhere('disbursement_type', '')
                      ->orWhere('disbursement_type', 'pending');
                });
            } else {
                $query->where('disbursement_type', $request->disbursement);
            }
        }

        $businessName = \App\Models\Tenant::first()?->business_name ?? '';
        $payments = $query->latest()->paginate(10)->through(function ($payment) use ($businessName) {
            $disb = $payment->disbursement_type ?? 'pending';
            $checkedBool = (bool)$payment->checked;
            $userDisplay = $payment->user_id === null ? 'Deleted User' : ($payment->user?->username ?? 'Unknown');
            return [
                'id'                 => $payment->id,
                'user'               => $userDisplay,
                'user_id'            => $payment->user_id,
                'phone'              => $payment->phone ?? ($payment->user?->phone ?? 'N/A'),
                'receipt_number'     => $payment->receipt_number,
                'amount'             => $payment->amount,
                'checked'            => $checkedBool,
                'paid_at'            => optional($payment->paid_at)->toDateTimeString(),
                'disbursement_type'  => $disb,
                'checked_label'      => $checkedBool ? 'Yes' : 'No',
                'disbursement_label' => ucfirst($disb),
                'business_name'      => $businessName,
            ];
        });

        // Get all payments for summary (no pagination)
        // Get all payments for summary (no pagination, ignore pagination and filters except search/disbursement)
        $allPayments = TenantPayment::query()->with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', fn($q2) =>
                    $q2->where('username', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%")
                )->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->when($request->disbursement, function ($q) use ($request) {
                if ($request->disbursement === 'pending') {
                    $q->where(function ($q2) {
                        $q2->whereNull('disbursement_type')
                          ->orWhere('disbursement_type', '')
                          ->orWhere('disbursement_type', 'pending');
                    });
                } else {
                    $q->where('disbursement_type', $request->disbursement);
                }
            })
            ->get()->map(function ($payment) use ($businessName) {
            $disb = $payment->disbursement_type ?? 'pending';
            $checkedBool = (bool)$payment->checked;
            return [
                'id'                 => $payment->id,
                'user'               => $payment->user?->username ?? 'Unknown',
                'user_id'            => $payment->user_id,
                'phone'              => $payment->phone ?? ($payment->user?->phone ?? 'N/A'),
                'receipt_number'     => $payment->receipt_number,
                'amount'             => $payment->amount,
                'checked'            => $checkedBool,
                'paid_at'            => optional($payment->paid_at)->toDateTimeString(),
                'disbursement_type'  => $disb,
                'checked_label'      => $checkedBool ? 'Yes' : 'No',
                'disbursement_label' => ucfirst($disb),
                'business_name'      => $businessName,
            ];
        });

        return Inertia::render('Tenants/Payments/Index', [
            'payments' => array_merge($payments->toArray(), ['allData' => $allPayments]),
            'filters'  => $request->only('search', 'disbursement'),
            'users'    => NetworkUser::select('id', 'username', 'phone')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'required|exists:network_users,id',
            'receipt_number'    => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0',
            'checked'           => 'required|boolean',   // 👈 boolean
            'paid_at'           => 'required|date',
            'disbursement_type' => 'required|string|in:pending,disbursed,withheld',
        ]);

        $user = NetworkUser::findOrFail($data['user_id']);
        if (Schema::hasColumn('tenant_payments', 'phone')) {
            $data['phone'] = $user->phone;
        }

        if (Schema::hasColumn('tenant_payments', 'created_by')) {
            $data['created_by'] = auth()->id();
        }

        $payment = TenantPayment::create($data);

        // Mikrotik suspend/unsuspend logic
        $routerHost = config('mikrotik.host', '192.168.88.1');
        $routerUser = config('mikrotik.username', 'admin');
        $routerPass = config('mikrotik.password', 'password');
        $routerPort = config('mikrotik.port', 8728);
        $mikrotik = new \App\Services\MikrotikService($routerHost, $routerUser, $routerPass, $routerPort);
        // If payment is pending/withheld, suspend user; if disbursed, unsuspend
        if (in_array($data['disbursement_type'], ['pending', 'withheld'])) {
            $mikrotik->suspendUser($user->type, $user->mikrotik_id ?? '');
        } elseif ($data['disbursement_type'] === 'disbursed') {
            $mikrotik->unsuspendUser($user->type, $user->mikrotik_id ?? '');
        }

        $this->sendPaymentSMS($payment);

        return back()->with('success', 'Payment added and SMS sent.');
    }

    public function update(Request $request, $id)
    {
        $tenantPayment = TenantPayment::findOrFail($id);

        $data = $request->validate([
            'user_id'           => 'sometimes|exists:network_users,id',
            'receipt_number'    => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0',
            'checked'           => 'required|boolean',   // 👈 boolean
            'paid_at'           => 'required|date',
            'disbursement_type' => 'required|string|in:pending,disbursed,withheld',
        ]);

        if (isset($data['user_id'])) {
            $user = NetworkUser::findOrFail($data['user_id']);
            if (Schema::hasColumn('tenant_payments', 'phone')) {
                $data['phone'] = $user->phone;
            }
        }

        $tenantPayment->update($data);

        // Mikrotik suspend/unsuspend logic
        $user = isset($data['user_id']) ? NetworkUser::findOrFail($data['user_id']) : $tenantPayment->user;
        $routerHost = config('mikrotik.host', '192.168.88.1');
        $routerUser = config('mikrotik.username', 'admin');
        $routerPass = config('mikrotik.password', 'password');
        $routerPort = config('mikrotik.port', 8728);
        $mikrotik = new \App\Services\MikrotikService($routerHost, $routerUser, $routerPass, $routerPort);
        if (in_array($data['disbursement_type'], ['pending', 'withheld'])) {
            $mikrotik->suspendUser($user->type, $user->mikrotik_id ?? '');
        } elseif ($data['disbursement_type'] === 'disbursed') {
            $mikrotik->unsuspendUser($user->type, $user->mikrotik_id ?? '');
        }

        return back()->with('success', 'Payment updated.');
    }

    public function destroy($id)
    {
        $tenantPayment = TenantPayment::findOrFail($id);
        $tenantPayment->delete();

        return back()->with('success', 'Payment deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No payments selected for deletion.');
        }
        TenantPayment::whereIn('id', $ids)->delete();
        return back()->with('success', 'Selected payments deleted successfully.');
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');

        return match ($format) {
            default => Excel::download(new TenantPaymentsExport, 'tenant_payments.xlsx'),
        };
    }

    private function sendPaymentSMS($payment)
    {
        $to = $payment->phone ?? $payment->user?->phone;

        if ($to) {
            try {
                Http::post('https://api.talksasa.com/send', [
                    'to'      => $to,
                    'message' => "Payment received: Receipt #{$payment->receipt_number}. Thank you.",
                    'apiKey'  => env('TALKSASA_API_KEY'),
                    'from'    => config('app.name'),
                ]);
            } catch (\Exception $e) {
                logger()->error('SMS failed: ' . $e->getMessage());
            }
        }
    }
}
