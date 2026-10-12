@extends('layout')
@section('title', 'Number '.$ticket->number)

@section('content')
@php($a = $ticket->answers ?? [])
<div class="page-head">
  <h1>Number {{ $ticket->number }}{{ $ticket->patient_name ? ' - '.$ticket->patient_name : '' }}</h1>
  <a class="btn btn-ghost" href="{{ route('staff.queue') }}">Back to queue</a>
</div>

@if($ticket->effective_priority === 'emergency' && $ticket->answers)
  <div class="alert alert-danger">Danger sign reported. Please see this patient right away.</div>
@endif

<div class="grid-2">
  <div class="card">
    <h2 class="h2" style="margin-top:0">Patient answers</h2>
    @if($ticket->answers === null)
      <p class="muted">The patient has not filled in the form yet.</p>
    @else
      <dl class="kv">
        <dt>Age group</dt><dd>{{ $ticket->age_label }}</dd>
        @if(! empty($a['pregnant']))<dt>Pregnant</dt><dd>Yes</dd>@endif
        @if(isset($a['duration']))<dt>How long</dt><dd>{{ config('triage.durations.'.$a['duration'], '-') }}</dd>@endif
        <dt>Symptoms</dt>
        <dd>
          @forelse($a['symptoms'] ?? [] as $s)
            <span class="tag">{{ config('triage.symptoms.'.$s, $s) }}</span>
          @empty
            -
          @endforelse
        </dd>
        <dt>Danger signs</dt>
        <dd>
          @forelse($a['danger'] ?? [] as $d)
            <span class="tag danger">{{ config('triage.danger.'.$d, $d) }}</span>
          @empty
            None reported
          @endforelse
        </dd>
        <dt>Notes</dt><dd>{{ $a['notes'] ?? '-' }}</dd>
      </dl>
    @endif
    <dl class="kv" style="margin-top:1rem">
      <dt>Arrived</dt><dd>{{ $ticket->created_at->format('g:i A') }} ({{ $ticket->source === 'staff' ? 'added by staff' : 'scanned QR' }})</dd>
      <dt>Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</dd>
    </dl>
  </div>

  <div class="card">
    <h2 class="h2" style="margin-top:0">Priority</h2>
    @if($ticket->fast_lane)<p><span class="badge fast">Fast lane</span></p>@endif
    @if($ticket->suggested_priority)
      <p>System suggestion: <span class="badge p-{{ $ticket->suggested_priority }}">{{ \App\Models\QueueTicket::PRIORITIES[$ticket->suggested_priority] }}</span></p>
      <ul class="muted" style="margin-top:0">
        @foreach($reasons as $r)<li>{{ $r }}</li>@endforeach
      </ul>
      <p class="muted" style="font-size:.85rem">This is a screening aid, not a diagnosis. Please confirm or change it.</p>
    @endif

    <form method="POST" action="{{ route('staff.ticket.priority', $ticket) }}">
      @csrf
      <div class="field">
        <div class="radios">
          @foreach(\App\Models\QueueTicket::PRIORITIES as $key => $label)
            <label><input type="radio" name="priority" value="{{ $key }}" @checked(old('priority', $ticket->effective_priority) === $key)><span class="p-{{ $key }}">{{ $label }}</span></label>
          @endforeach
        </div>
      </div>
      <div class="field">
        <label for="note">Reason (needed if you change the suggestion)</label>
        <input id="note" type="text" name="note" maxlength="255" value="{{ old('note', $ticket->priority_note) }}">
        @error('note')<div class="err">{{ $message }}</div>@enderror
      </div>
      @php($fl = (string) old('fast_lane', $ticket->fast_lane ? '1' : '0'))
      <div class="field">
        <span class="legend">Fast lane (suspected notifiable disease)</span>
        <div class="radios">
          <label><input type="radio" name="fast_lane" value="0" @checked($fl === '0')><span class="fl-no">No</span></label>
          <label><input type="radio" name="fast_lane" value="1" @checked($fl === '1')><span class="fl-yes">Yes</span></label>
        </div>
        <p class="muted" style="font-size:.85rem;margin:.4rem 0 0">Choose Yes only for a suspected notifiable disease. After you confirm, the patient sees a button to register in LIGTAS.</p>
      </div>
      <button class="btn btn-primary">{{ $ticket->is_confirmed ? 'Update priority' : 'Confirm priority' }}</button>
    </form>
  </div>
</div>

<h2 class="h2">Actions</h2>
<div class="actions">
  @if($ticket->status === 'waiting')
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'called', 'label' => 'Call patient', 'class' => 'btn-amber'])
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'cancelled', 'label' => 'Cancel ticket', 'class' => 'btn-ghost'])
  @elseif($ticket->status === 'called')
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'done', 'label' => 'Mark done', 'class' => 'btn-primary'])
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'no_show', 'label' => 'No show', 'class' => 'btn-ghost'])
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'waiting', 'label' => 'Back to queue', 'class' => 'btn-ghost'])
  @else
    @include('staff.partials.status-button', ['t' => $ticket, 'to' => 'waiting', 'label' => 'Put back in queue', 'class' => 'btn-ghost'])
  @endif
</div>
@endsection
