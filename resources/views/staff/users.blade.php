@extends('layout')
@section('title', 'Staff accounts')

@section('content')
<div class="page-head"><h1>Staff accounts</h1></div>

<div class="grid-2">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->role === 'admin' ? 'Admin' : 'Triage staff' }}</td>
          <td>
            @if($u->id !== auth()->id())
              <form method="POST" action="{{ route('staff.users.destroy', $u) }}" onsubmit="return confirm('Remove this account?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm">Remove</button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <form class="card" method="POST" action="{{ route('staff.users.store') }}">
    @csrf
    <h2 class="h2" style="margin-top:0">New account</h2>
    <div class="field"><label for="name">Name</label><input id="name" type="text" name="name" value="{{ old('name') }}" required>@error('name')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="password">Password (8 characters or more)</label><input id="password" type="password" name="password" required>@error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="field">
      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="triage_staff">Triage staff</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <button class="btn btn-primary">Create account</button>
  </form>
</div>
@endsection
