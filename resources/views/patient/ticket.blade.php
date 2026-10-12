<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your queue number | LIGTriage</title>
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/ligtriage.css') }}">
</head>
<body>
@php($a = $ticket->answers ?? [])
@php($open = in_array($ticket->status, ['waiting', 'called'], true))
<div class="patient">
  <div class="p-head">
    <span class="logo-chip"><img src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo"></span>
    <div><b>LIGTriage</b><small>Companion system of LIGTAS</small></div>
  </div>

  <div class="num-card">
    <small>Your queue number</small>
    <div class="num" id="num">{{ $ticket->number }}</div>
  </div>

  <div class="card" id="panel" aria-live="polite"></div>

  @if($open)
    @if($editing && $ticket->status === 'waiting')
      <form class="card" id="formBox" style="margin-top:1rem" method="POST" action="{{ route('ticket.submit', $ticket->access_token) }}">
        @csrf
        <h2 style="margin-bottom:.3rem">While you wait</h2>
        <p class="muted" style="margin-top:0">Answer these so the staff can see you quickly. It takes about a minute.</p>

        <div class="field">
          <label for="name">Name or nickname (optional)</label>
          <input id="name" type="text" name="name" maxlength="80" value="{{ old('name', $ticket->patient_name) }}">
        </div>

        <div class="field">
          <label for="age_group">Age</label>
          <select id="age_group" name="age_group" required>
            <option value="">Choose</option>
            @foreach(config('triage.age_groups') as $key => $label)
              <option value="{{ $key }}" @selected(old('age_group', $a['age_group'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
          </select>
          @error('age_group')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="check"><input type="checkbox" name="pregnant" value="1" @checked(old('pregnant', $a['pregnant'] ?? false))> I am pregnant</label>
        </div>

        <div class="danger-box">
          <span class="legend">Do you have any of these right now?</span>
          <div class="checks">
            @foreach(config('triage.danger') as $key => $label)
              <label class="check"><input type="checkbox" name="danger[]" value="{{ $key }}" @checked(in_array($key, old('danger', $a['danger'] ?? []), true))> {{ $label }}</label>
            @endforeach
          </div>
        </div>

        <div class="field">
          <span class="legend">What are your symptoms?</span>
          <div class="checks">
            @foreach(config('triage.symptoms') as $key => $label)
              <label class="check"><input type="checkbox" name="symptoms[]" value="{{ $key }}" @checked(in_array($key, old('symptoms', $a['symptoms'] ?? []), true))> {{ $label }}</label>
            @endforeach
          </div>
          @error('symptoms')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="duration">How long have you had them?</label>
          <select id="duration" name="duration" required>
            <option value="">Choose</option>
            @foreach(config('triage.durations') as $key => $label)
              <option value="{{ $key }}" @selected(old('duration', $a['duration'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
          </select>
          @error('duration')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="notes">Anything else? (optional)</label>
          <textarea id="notes" name="notes" maxlength="255">{{ old('notes', $a['notes'] ?? '') }}</textarea>
        </div>

        <div class="field">
          <label class="check"><input type="checkbox" name="consent" value="1" @checked(old('consent'))> I agree to share these answers with the CHO triage staff for this visit only.</label>
          @error('consent')<div class="err">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-primary btn-wide">Send my answers</button>
      </form>
    @elseif($ticket->status === 'waiting')
      <p style="margin-top:1rem;text-align:center"><a href="{{ route('ticket.show', $ticket->access_token) }}?edit=1">Change my answers</a></p>
    @endif

    @if($ticket->status === 'waiting')
      <form method="POST" action="{{ route('ticket.cancel', $ticket->access_token) }}" onsubmit="return confirm('Leave the queue? You will need to scan again to get a new number.')" style="text-align:center;margin-top:1rem">
        @csrf
        <button class="btn btn-ghost btn-sm">I am leaving the queue</button>
      </form>
    @endif
  @endif

  <p class="foot"><img src="{{ asset('images/ligtas-logo.png') }}" alt="">LIGTriage works alongside LIGTAS</p>
</div>

<script>
const statusUrl = @json(route('ticket.status', $ticket->access_token));
const panel = document.getElementById('panel');
const formBox = document.getElementById('formBox');
const labels = { emergency: 'Emergency', urgent: 'Urgent', routine: 'Routine' };

function render(d) {
  document.getElementById('num').textContent = d.number;
  let h = '';

  if (d.status === 'called') {
    h += '<div class="alert alert-ok" style="margin:0">It is your turn. Please go to the triage area now.</div>';
  } else if (d.status === 'waiting') {
    const word = d.ahead === 1 ? 'person' : 'people';
    h += '<div class="queue-line"><span class="big">' + d.ahead + '</span> ' + word + ' ahead of you</div>';
    if (d.wait_minutes > 0) h += '<p class="muted" style="margin:.2rem 0 0">Estimated wait: about ' + d.wait_minutes + ' minutes</p>';
  } else if (d.status === 'done') {
    h += '<p style="margin:0"><b>Your visit is finished.</b> Thank you.</p>';
  } else if (d.status === 'no_show') {
    h += '<p style="margin:0"><b>You were not here when your number was called.</b> Please scan the QR at the entrance again to get a new number.</p>';
  } else {
    h += '<p style="margin:0"><b>This ticket was cancelled.</b> Scan the QR at the entrance if you still need to be seen.</p>';
  }

  if (d.priority && (d.status === 'waiting' || d.status === 'called')) {
    h += '<p style="margin:.8rem 0 0">Priority: <span class="badge p-' + d.priority + '">' + labels[d.priority] + '</span>'
      + '<span class="hint">' + (d.confirmed ? 'confirmed by staff' : 'waiting for staff to confirm') + '</span></p>';
  }

  if (d.priority === 'emergency' && d.status === 'waiting') {
    h += '<div class="alert alert-danger" style="margin:.8rem 0 0">Please tell the triage staff right now. Do not wait for your number.</div>';
  }

  if (d.fast_lane && (d.status === 'waiting' || d.status === 'called')) {
    h += '<div class="fast-box"><b>Fast lane</b><p>The triage staff marked you for the fast lane. Please register in LIGTAS now.</p>'
      + '<a class="btn btn-amber btn-wide" href="' + d.register_url + '" target="_blank" rel="noopener">Register in LIGTAS</a></div>';
  }

  panel.innerHTML = h;

  if (formBox && d.status !== 'waiting') formBox.hidden = true;
}

render(@json($status));
setInterval(async () => {
  try {
    const res = await fetch(statusUrl, { cache: 'no-store' });
    if (res.ok) render(await res.json());
  } catch (e) {}
}, 4000);
</script>
</body>
</html>
