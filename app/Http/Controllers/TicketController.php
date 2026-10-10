<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Support\TriageRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

# patient side, reached by scanning the entrance QR. no login
class TicketController extends Controller
{
    public function show(string $accessToken)
    {
        QueueTicket::expireStale();
        $ticket = $this->find($accessToken);

        return view('patient.ticket', [
            'ticket' => $ticket,
            'status' => $this->payload($ticket),
            'editing' => request()->boolean('edit') || $ticket->answers === null,
        ]);
    }

    public function status(string $accessToken): JsonResponse
    {
        QueueTicket::expireStale();

        return response()->json($this->payload($this->find($accessToken)));
    }

    public function submit(Request $request, string $accessToken)
    {
        $ticket = $this->find($accessToken);

        if ($ticket->status !== 'waiting') {
            return redirect()->route('ticket.show', $accessToken);
        }

        $data = $request->validate([
            'name' => 'nullable|string|max:80',
            'age_group' => 'required|in:'.implode(',', array_keys(config('triage.age_groups'))),
            'pregnant' => 'nullable|boolean',
            'symptoms' => 'required_without:danger|nullable|array',
            'symptoms.*' => 'in:'.implode(',', array_keys(config('triage.symptoms'))),
            'duration' => 'required|in:'.implode(',', array_keys(config('triage.durations'))),
            'danger' => 'nullable|array',
            'danger.*' => 'in:'.implode(',', array_keys(config('triage.danger'))),
            'notes' => 'nullable|string|max:255',
            'consent' => 'accepted',
        ], [
            'symptoms.required_without' => 'Pick at least one symptom or danger sign.',
            'consent.accepted' => 'Please tick the box to share your answers with the triage staff.',
        ]);

        $answers = [
            'age_group' => $data['age_group'],
            'pregnant' => $request->boolean('pregnant'),
            'symptoms' => array_values($data['symptoms'] ?? []),
            'duration' => $data['duration'],
            'danger' => array_values($data['danger'] ?? []),
            'notes' => $data['notes'] ?? null,
        ];

        [$suggested] = TriageRules::suggest($answers);

        $ticket->update([
            'patient_name' => $data['name'] ?? null,
            'answers' => $answers,
            'suggested_priority' => $suggested,
        ]);

        return redirect()->route('ticket.show', $accessToken);
    }

    public function cancel(string $accessToken)
    {
        $ticket = $this->find($accessToken);

        if ($ticket->status === 'waiting') {
            $ticket->update(['status' => 'cancelled', 'finished_at' => now()]);
        }

        return redirect()->route('ticket.show', $accessToken);
    }

    private function find(string $accessToken): QueueTicket
    {
        return QueueTicket::where('access_token', $accessToken)->firstOrFail();
    }

    private function payload(QueueTicket $ticket): array
    {
        $ahead = null;
        $wait = null;

        if ($ticket->status === 'waiting') {
            $ids = QueueTicket::today()->where('status', 'waiting')->ordered()->pluck('id')->all();
            $pos = array_search($ticket->id, $ids);
            $ahead = $pos === false ? 0 : $pos;
            $wait = $ahead * QueueTicket::avgHandlingMinutes();
        }

        $hasPriority = $ticket->priority !== null || $ticket->suggested_priority !== null;

        return [
            'number' => $ticket->number,
            'status' => $ticket->status,
            'has_form' => $ticket->answers !== null,
            'priority' => $hasPriority ? $ticket->effective_priority : null,
            'confirmed' => $ticket->is_confirmed,
            'ahead' => $ahead,
            'wait_minutes' => $wait,
        ];
    }
}
