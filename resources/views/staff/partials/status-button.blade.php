<form method="POST" action="{{ route('staff.ticket.status', $t) }}">
  @csrf
  <input type="hidden" name="status" value="{{ $to }}">
  <button class="btn btn-sm {{ $class }}">{{ $label }}</button>
</form>
