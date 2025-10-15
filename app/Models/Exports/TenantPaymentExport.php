<?php

namespace App\Models\Exports;

use App\Models\Tenants\TenantPayment;
use Maatwebsite\Excel\Concerns\FromCollection;

class TenantPaymentExport implements FromCollection
{
    public function collection()
    {
        return TenantPayment::with('user')->get()->map(function ($payment) {
            return [
                'User' => $payment->user?->username ?? 'Unknown',
                'Phone' => $payment->user?->phone ?? 'N/A',
                'Receipt Number' => $payment->receipt_number,
                'Checked' => $payment->checked ? 'Yes' : 'No',
                'Paid At' => $payment->paid_at->toDateTimeString(),
                'Disbursement' => $payment->disbursement_type ?? 'Pending',
            ];
        });
    }
}
