<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $date = $request->filled('date') ? \Carbon\Carbon::parse($request->input('date')) : today();
        $tickets = QueueTicket::whereDate('queue_date', $date)->get();

        $waits = $tickets->map(fn ($t) => $t->waitMinutes())->filter(fn ($m) => $m !== null);

        $handling = $tickets
            ->filter(fn ($t) => $t->status === 'done' && $t->called_at && $t->finished_at)
            ->map(fn ($t) => max(0, intdiv($t->finished_at->timestamp - $t->called_at->timestamp, 60)));

        $confirmed = $tickets->filter(fn ($t) => $t->suggested_priority && $t->priority);
        $changed = $confirmed->filter(fn ($t) => $t->suggested_priority !== $t->priority)->count();

        $byHour = $tickets->groupBy(fn ($t) => (int) $t->created_at->format('G'))->map->count();
        $hours = range(
            min(6, $byHour->keys()->min() ?? 6),
            max(18, $byHour->keys()->max() ?? 18)
        );

        return view('staff.summary', [
            'date' => $date,
            'total' => $tickets->count(),
            'byStatus' => $tickets->countBy('status'),
            'byPriority' => $tickets->countBy(fn ($t) => $t->effective_priority),
            'bySource' => $tickets->countBy('source'),
            'avgWait' => $waits->isNotEmpty() ? (int) round($waits->avg()) : null,
            'maxWait' => $waits->isNotEmpty() ? $waits->max() : null,
            'avgHandling' => $handling->isNotEmpty() ? (int) round($handling->avg()) : null,
            'confirmedCount' => $confirmed->count(),
            'changedCount' => $changed,
            'byHour' => $byHour,
            'tickets' => $tickets->sortBy('number')->values(),
            'hours' => $hours,
        ]);
    }
}
