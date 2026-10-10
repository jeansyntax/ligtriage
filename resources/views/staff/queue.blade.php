@extends('layout')
@section('title', 'Queue')

@section('content')
<div class="page-head">
  <h1>Today's queue</h1>
  <span class="muted">{{ now()->format('l, F j, Y') }}. Updates by itself.</span>
</div>

<div id="board">@include('staff.partials.queue')</div>
@endsection

@push('scripts')
<script>
const partialUrl = @json(route('staff.queue.partial'));
const board = document.getElementById('board');
async function refresh() {
  try {
    const res = await fetch(partialUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
    if (res.status === 401 || res.status === 419) { location.reload(); return; }
    if (res.ok) board.innerHTML = await res.text();
  } catch (e) {}
}
setInterval(refresh, 3000);
</script>
@endpush
