@extends('layout')
@section('title', 'Add patient')

@section('content')
<div class="page-head">
  <h1>Add a patient without a phone</h1>
  <a class="btn btn-ghost" href="{{ route('staff.queue') }}">Back to queue</a>
</div>

<form class="card" style="max-width:34rem" method="POST" action="{{ route('staff.store') }}">
  @csrf
  <div class="field">
    <label for="name">Name (optional)</label>
    <input id="name" type="text" name="name" maxlength="80" value="{{ old('name') }}">
  </div>
  <div class="field">
    <label for="age_group">Age group</label>
    <select id="age_group" name="age_group" required>
      <option value="">Choose</option>
      @foreach(config('triage.age_groups') as $key => $label)
        <option value="{{ $key }}" @selected(old('age_group') === $key)>{{ $label }}</option>
      @endforeach
    </select>
    @error('age_group')<div class="err">{{ $message }}</div>@enderror
  </div>
  <div class="field">
    <label for="complaint">Main complaint</label>
    <textarea id="complaint" name="complaint" maxlength="255">{{ old('complaint') }}</textarea>
  </div>
  <div class="field">
    <span class="legend">Priority</span>
    <div class="radios">
      @foreach(\App\Models\QueueTicket::PRIORITIES as $key => $label)
        <label><input type="radio" name="priority" value="{{ $key }}" @checked(old('priority', 'routine') === $key)><span class="p-{{ $key }}">{{ $label }}</span></label>
      @endforeach
    </div>
  </div>
  <div class="field">
    <label class="check"><input type="checkbox" name="fast_lane" value="1" @checked(old('fast_lane'))> Fast lane (suspected notifiable disease)</label>
  </div>
  <button class="btn btn-primary">Add to queue</button>
</form>
@endsection
