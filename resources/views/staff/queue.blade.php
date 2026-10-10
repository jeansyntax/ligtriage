@extends('layout')
@section('title', 'Queue')

@section('content')
<section class="hero">
  <span class="pill">CHO Triage Queue · San Fernando City, La Union</span>
  <h1>Today's <span>queue</span></h1>
  <p>{{ now()->format('l, F j, Y') }}. This board updates by itself, so you do not need to refresh it.</p>
</section>

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
