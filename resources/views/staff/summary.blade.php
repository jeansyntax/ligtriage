@extends('layout')
@section('title', 'Daily summary')

@section('content')
<div class="print-only report-head">
  <img src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo">
  <div>
    <h1>LIGTriage daily report</h1>
    <p>CHO triage queue, companion system of LIGTAS</p>
    <p>{{ $date->format('l, F j, Y') }}. Prepared by {{ auth()->user()->name }}, printed {{ now()->format('g:i A') }}.</p>
  </div>
</div>

<div class="page-head no-print">
  <h1>Daily summary</h1>
  <form method="GET" action="{{ route('staff.summary') }}" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
    <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" style="width:auto">
    <button class="btn btn-primary btn-sm">Show</button>
    <button type="button" class="btn btn-amber btn-sm" onclick="window.print()">Print report</button>
  </form>
</div>
<p class="muted no-print" style="margin-top:-.6rem">{{ $date->format('l, F j, Y') }}</p>

<div class="stats">
  <div class="stat"><span class="stat-n">{{ $total }}</span><span class="stat-l">Tickets issued</span></div>
  <div class="stat"><span class="stat-n">{{ $byStatus->get('done', 0) }}</span><span class="stat-l">Patients seen</span></div>
  <div class="stat"><span class="stat-n">{{ $avgWait !== null ? $avgWait.' min' : '-' }}</span><span class="stat-l">Average wait</span></div>
  <div class="stat"><span class="stat-n">{{ $maxWait !== null ? $maxWait.' min' : '-' }}</span><span class="stat-l">Longest wait</span></div>
  <div class="stat"><span class="stat-n">{{ $avgHandling !== null ? $avgHandling.' min' : '-' }}</span><span class="stat-l">Average time with staff</span></div>
</div>

<div class="grid-2">
  <div class="card">
    <h2 class="h2" style="margin-top:0">By priority</h2>
    <table>
      @foreach(\App\Models\QueueTicket::PRIORITIES as $key => $label)
        <tr><td><span class="badge p-{{ $key }}">{{ $label }}</span></td><td><b>{{ $byPriority->get($key, 0) }}</b></td></tr>
      @endforeach
    </table>
  </div>

  <div class="card">
    <h2 class="h2" style="margin-top:0">Outcome</h2>
    <table>
      <tr><td>Done</td><td><b>{{ $byStatus->get('done', 0) }}</b></td></tr>
      <tr><td>No show</td><td><b>{{ $byStatus->get('no_show', 0) }}</b></td></tr>
      <tr><td>Cancelled</td><td><b>{{ $byStatus->get('cancelled', 0) }}</b></td></tr>
      <tr><td>Still waiting or being served</td><td><b>{{ $byStatus->get('waiting', 0) + $byStatus->get('called', 0) }}</b></td></tr>
    </table>
  </div>

  <div class="card">
    <h2 class="h2" style="margin-top:0">How tickets were made</h2>
    <table>
      <tr><td>Scanned the entrance QR</td><td><b>{{ $bySource->get('self', 0) }}</b></td></tr>
      <tr><td>Added by staff</td><td><b>{{ $bySource->get('staff', 0) }}</b></td></tr>
    </table>
  </div>

  <div class="card">
    <h2 class="h2" style="margin-top:0">Screening check</h2>
    @if($confirmedCount > 0)
      <p style="margin-top:0">Staff kept the suggested priority for
        <b>{{ $confirmedCount - $changedCount }} of {{ $confirmedCount }}</b> confirmed tickets
        ({{ (int) round((($confirmedCount - $changedCount) / $confirmedCount) * 100) }}%).</p>
      <p class="muted" style="margin-bottom:0">Changed by staff: {{ $changedCount }}</p>
    @else
      <p class="muted" style="margin:0">No confirmed tickets with a suggestion yet.</p>
    @endif
  </div>
</div>

<h2 class="h2">Tickets by hour</h2>
<div class="card">
  <div class="bars">
    @php($peak = max(1, $byHour->max() ?? 1))
    @foreach($hours as $h)
      @php($count = $byHour->get($h, 0))
      <div class="bar">
        <b>{{ $count ?: '' }}</b>
        <i style="height:{{ ($count / $peak) * 100 }}%"></i>
        <small>{{ $h }}h</small>
      </div>
    @endforeach
  </div>
</div>

<h2 class="h2">Ticket log</h2>
<div class="table-wrap">
  <table>
    <thead><tr><th>No.</th><th>Arrived</th><th>Priority</th><th>Confirmed by staff</th><th>Wait</th><th>Outcome</th></tr></thead>
    <tbody>
    @forelse($tickets as $t)
      <tr>
        <td>{{ $t->number }}</td>
        <td>{{ $t->created_at->format('g:i A') }}</td>
        <td>{{ $t->priority_label }}</td>
        <td>{{ $t->is_confirmed ? 'Yes' : 'No' }}</td>
        <td>{{ $t->waitMinutes() !== null ? $t->waitMinutes().' min' : '-' }}</td>
        <td>{{ ucfirst(str_replace('_', ' ', $t->status)) }}</td>
      </tr>
    @empty
      <tr><td colspan="6" class="muted">No tickets on this day.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@endsection
