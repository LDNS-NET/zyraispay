<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantSMS;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Tenants\NetworkUser;
use App\Models\Tenants\TenantSMSTemplate;
use Illuminate\Support\Facades\Log;
use App\Services\AfricaTalkingService;

class TenantSMSController extends Controller
{
    protected float $smsCostPerCharacter = 0.01; // 0.01 = 1 cent per character

    /**
     * List all SMS messages created by the authenticated user.
     */
    public function index(AfricaTalkingService $smsService)
    {
        $messages = TenantSMS::where('created_by', auth()->id())->latest()->paginate(10);
        $balanceResult = $smsService->getBalance();
        $balance = floatval(preg_replace('/[^0-9.]/', '', $balanceResult['balance'] ?? '0'));
        $subscribers = NetworkUser::get(['id', 'full_name', 'phone']);

        return Inertia::render('Tenants/SMS/Index', [
            'messages' => $messages,
            'balance' => $balance,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Store and send SMS messages.
     */
    public function store(Request $request, AfricaTalkingService $smsService)
    {
        Log::info('TenantSMSController@store called', ['user_id' => auth()->id()]);
        $validated = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);
        Log::info('Validation passed', $validated);

        $balanceResult = $smsService->getBalance();
        $balance = floatval(preg_replace('/[^0-9.]/', '', $balanceResult['balance'] ?? '0'));
        Log::info('AfricaTalking balance fetched', ['balance' => $balance]);

        $characters = strlen($validated['message']);
        $costPerMessage = $characters * $this->smsCostPerCharacter;
        $totalCost = $costPerMessage * count($validated['recipients']);
        Log::info('Cost calculated', ['characters' => $characters, 'costPerMessage' => $costPerMessage, 'totalCost' => $totalCost]);

        if ($balance < $totalCost) {
            Log::warning('Insufficient AfricaTalking balance', ['balance' => $balance, 'totalCost' => $totalCost]);
            return back()->withErrors(['sms' => 'Insufficient SMS balance']);
        }

        foreach ($validated['recipients'] as $recipient) {
            $user = NetworkUser::where('phone', $recipient)->first();
            $response = $smsService->sendSMS([$recipient], $validated['message']);
            $status = $response['status'] ?? 'unknown';

            $sms = TenantSMS::create([
                'recipient' => $user?->full_name ?? 'Unknown',
                'recipient_phone' => $recipient,
                'message' => $validated['message'],
                'characters' => $characters,
                'cost' => $costPerMessage,
                'status' => $status,
                'created_by' => auth()->id(),
                'response' => $response,
                'sent_at' => now(),
            ]);
            Log::info('SMS record created', ['sms_id' => $sms->id, 'status' => $status, 'response' => $response]);
        }

        return back()->with('success', 'Messages sent successfully.');
    }

    /**
     * Update an existing SMS record.
     */
    public function update(Request $request, TenantSMS $sms)
    {
        $this->authorizeAccess($sms);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'status' => ['required', Rule::in(['pending', 'sent', 'failed'])],
        ]);

        $sms->update($validated);

        return back()->with('success', 'SMS updated successfully.');
    }

    /**
     * Delete a specific SMS.
     */
    public function destroy(TenantSMS $sms)
    {
        $this->authorizeAccess($sms);
        $sms->delete();

        return back()->with('success', 'SMS deleted.');
    }

    /**
     * Bulk delete SMS messages.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tenant_sms,id',
        ]);

        TenantSMS::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected SMS messages deleted.');
    }

    /**
     * Restrict access to only the creator.
     */
    protected function authorizeAccess(TenantSMS $sms): void
    {
        if ($sms->created_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
