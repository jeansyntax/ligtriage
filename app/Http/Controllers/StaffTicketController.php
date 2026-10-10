<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Support\TriageRules;
use Illuminate\Http\Request;

class StaffTicketController extends Controller
{
    public function show(QueueTicket $ticket)
    {
        $reasons = $ticket->answers && $ticket->suggested_priority
            ? TriageRules::suggest($ticket->answers)[1]
            : [];

        return view('staff.ticket', compact('ticket', 'reasons'));
    }

    public function priority(Request $request, QueueTicket $ticket)
    {
        $data = $request->validate([
            'priority' => 'required|in:emergency,urgent,routine',
            'note' => 'nullable|string|max:255',
        ]);

        $changed = $ticket->suggested_priority && $data['priority'] !== $ticket->suggested_priority;

        if ($changed && blank($data['note'] ?? null)) {
            return back()
                ->withErrors(['note' => 'Please add a short reason for changing the suggested priority.'])
                ->withInput();
        }

        $ticket->update([
            'priority' => $data['priority'],
            'priority_note' => $data['note'] ?? null,
        ]);

        return back()->with('ok', 'Priority confirmed.');
    }

    public function status(Request $request, QueueTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:waiting,called,done,no_show,cancelled',
        ]);

        $to = $data['status'];
        $changes = ['status' => $to];

        if ($to === 'waiting') {
            $changes += ['called_at' => null, 'finished_at' => null];
        } elseif ($to === 'called') {
            $changes += ['called_at' => now(), 'finished_at' => null];
        } else {
            $changes['finished_at'] = now();
            if ($to === 'done' && ! $ticket->called_at) {
                $changes['called_at'] = now();
            }
        }

        $ticket->update($changes);

        return back()->with('ok', "Number {$ticket->number} updated.");
    }

    public function create()
    {
        return view('staff.create');
    }

    # for patients without a phone
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:80',
            'age_group' => 'required|in:'.implode(',', array_keys(config('triage.age_groups'))),
            'complaint' => 'nullable|string|max:255',
            'priority' => 'required|in:emergency,urgent,routine',
        ]);

        $ticket = QueueTicket::createForToday([
            'source' => 'staff',
            'patient_name' => $data['name'] ?? null,
            'answers' => [
                'age_group' => $data['age_group'],
                'symptoms' => [],
                'danger' => [],
                'notes' => $data['complaint'] ?? null,
            ],
            'priority' => $data['priority'],
        ]);

        return redirect()->route('staff.queue')->with('ok', "Added as number {$ticket->number}.");
    }
}
