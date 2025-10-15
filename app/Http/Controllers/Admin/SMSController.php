<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\AfricaTalkingService;

class SMSController extends Controller
{
    // Display SMS messages with filters
    public function index(Request $request, AfricaTalkingService $smsService)
{
    $query = SmsMessage::with('user');

    // Filter by user ID
    if ($request->filled('user')) {
        $query->where('user_id', $request->user);
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter by date range
    if ($request->filled('from')) {
        $query->whereDate('sent_at', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $query->whereDate('sent_at', '<=', $request->to);
    }

    // 🔍 Search by recipient name or phone
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('recipient_name', 'like', "%{$search}%")
              ->orWhere('recipient_phone', 'like', "%{$search}%");
        });
    }

    // Fetch balance
    $balanceResult = $smsService->getBalance();
    $balance = $balanceResult['balance'] ?? 'Unavailable';

    return Inertia::render('Admin/SMS/Index', [
        'smsList' => $query->orderByDesc('sent_at')->paginate(10)
            ->through(fn($msg) => [
                'id' => $msg->id,
                'recipient_name' => $msg->recipient_name,
                'recipient_phone' => $msg->recipient_phone,
                'message' => $msg->message,
                'status' => $msg->status,
                'sent_at' => $msg->sent_at,
                'user' => $msg->user ? [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name
                ] : null,
            ]),
        'users' => User::where('is_super_admin', false)
                    ->select('id', 'name', 'phone')
                    ->get(),
        'filters' => $request->only(['user', 'status', 'from', 'to', 'search']),
        'balance' => $balance,
    ]);
}



    // Send SMS to one or more users
    public function send(Request $request, AfricaTalkingService $smsService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
        ]);

        $senderId = auth()->id(); // ✅ Get current sender's ID

        foreach ($validated['recipients'] as $phone) {
            $user = User::where('phone', $phone)->first();

            $response = $smsService->sendSMS([$phone], $validated['message']);
            $status = $response['status'] ?? 'unknown';

            SmsMessage::create([
                'user_id' => $senderId, // ✅ Now this is the sender
                'recipient_name' => $user?->name ?? 'Unknown',
                'recipient_phone' => $phone,
                'message' => $validated['message'],
                'sent_at' => now(),
                'status' => $status,
                'response' => json_encode($response),
            ]);
        }

        return redirect()->route('admin.sms.index')->with('success', 'SMS sent successfully.');
    }


    // Delete a single SMS record
    public function destroy(SmsMessage $sms)
    {
        $sms->delete();

        return redirect()->route('admin.sms.index')->with('success', 'SMS deleted successfully.');

    }

    // Bulk delete selected SMS records
    public function destroyBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:sms_messages,id',
        ]);

        SmsMessage::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.sms.index')->with('success', 'Selected SMS messages deleted successfully.');

    }

    // Export all SMS logs as CSV
    public function export(Request $request)
    {
        $filename = 'sms_logs_' . now()->format('Ymd_His') . '.csv';
        $messages = SmsMessage::with('user')->orderByDesc('sent_at')->get();

        $headers = ['Content-Type' => 'text/csv'];
        $columns = ['ID', 'Recipient Name', 'Recipient Phone', 'Message', 'Status', 'Sent At', 'Sent By'];

        $callback = function () use ($messages, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($messages as $msg) {
                fputcsv($file, [
                    $msg->id,
                    $msg->recipient_name,
                    $msg->recipient_phone,
                    $msg->message,
                    $msg->status,
                    $msg->sent_at,
                    $msg->user->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, array_merge($headers, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]));
    }
}
