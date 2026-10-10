<?php

namespace App\Http\Controllers;

use App\Models\QrToken;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function scan(Request $request, string $token)
    {
        $deviceToken = $request->cookie('queue_device');

        # same phone scanning again gets its own ticket back
        if ($deviceToken) {
            $existing = QueueTicket::today()->active()->where('device_token', $deviceToken)->first();
            if ($existing) {
                return redirect()->route('ticket.show', $existing->access_token);
            }
        }

        $ticket = DB::transaction(function () use ($token, $deviceToken) {
            $qr = QrToken::where('token', $token)->lockForUpdate()->first();

            if (! $qr || $qr->used_at || $qr->expires_at->isPast()) {
                return null;
            }

            $ticket = QueueTicket::createForToday([
                'device_token' => $deviceToken ?: Str::random(40),
                'source' => 'self',
            ]);

            $qr->update(['used_at' => now(), 'ticket_id' => $ticket->id]);

            return $ticket;
        });

        if (! $ticket) {
            return response()->view('scan-invalid', [], 410);
        }

        return redirect()
            ->route('ticket.show', $ticket->access_token)
            ->withCookie(cookie('queue_device', $ticket->device_token, 60 * 12));
    }
}
