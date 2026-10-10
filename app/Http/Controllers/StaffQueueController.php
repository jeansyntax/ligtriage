<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;

class StaffQueueController extends Controller
{
    public function index()
    {
        return view('staff.queue', $this->data());
    }

    # the board swaps this in every few seconds
    public function partial()
    {
        return view('staff.partials.queue', $this->data());
    }

    public function callNext()
    {
        QueueTicket::expireStale();

        $next = QueueTicket::today()->where('status', 'waiting')->ordered()->first();

        if (! $next) {
            return back()->with('ok', 'Nobody is waiting right now.');
        }

        $next->update(['status' => 'called', 'called_at' => now()]);

        return back()->with('ok', "Calling number {$next->number}.");
    }

    private function data(): array
    {
        QueueTicket::expireStale();

        $today = QueueTicket::today()->get();
        $waits = $today->map(fn ($t) => $t->waitMinutes())->filter(fn ($m) => $m !== null);

        return [
            'waiting' => QueueTicket::today()->where('status', 'waiting')->ordered()->get(),
            'called' => QueueTicket::today()->where('status', 'called')->orderBy('called_at')->get(),
            'doneCount' => $today->where('status', 'done')->count(),
            'avgWait' => $waits->isNotEmpty() ? (int) round($waits->avg()) : null,
        ];
    }
}
