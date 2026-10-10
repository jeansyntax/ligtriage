<?php

namespace App\Http\Controllers;

use App\Models\QrToken;
use Illuminate\Http\JsonResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EntranceController extends Controller
{
    public function show()
    {
        return view('entrance');
    }

    # polled by the entrance screen every couple of seconds
    public function current(): JsonResponse
    {
        $token = QrToken::usable()
            ->where('expires_at', '>', now()->addSeconds(QrToken::SWAP_EARLY))
            ->orderBy('id')
            ->first() ?? QrToken::issue();

        $svg = QrCode::format('svg')
            ->size(400)
            ->margin(0)
            ->errorCorrection('M')
            ->generate(route('scan', $token->token));

        # a code used in the last few seconds means a ticket was just issued
        $justUsed = QrToken::whereNotNull('used_at')
            ->where('used_at', '>', now()->subSeconds(5))
            ->latest('used_at')
            ->with('ticket')
            ->first();

        return response()->json([
            'id' => $token->id,
            'svg' => (string) $svg,
            'expires_in' => max(0, $token->expires_at->timestamp - now()->timestamp - QrToken::SWAP_EARLY),
            'lifetime' => QrToken::LIFETIME - QrToken::SWAP_EARLY,
            'issued' => $justUsed?->ticket?->number,
        ]);
    }
}
