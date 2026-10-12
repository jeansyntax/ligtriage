@extends('layout')
@section('title', 'CHO staff')

@section('content')
<div class="page-head"><h1>CHO staff accounts</h1></div>

<div class="grid-2">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->role === 'admin' ? 'Admin' : 'CHO staff' }}</td>
          <td>
            @if($u->role !== 'admin')
              <button type="button" class="btn btn-ghost btn-sm js-delete"
                data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                data-action="{{ route('staff.users.destroy', $u) }}">Remove</button>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <form class="card" id="createForm" method="POST" action="{{ route('staff.users.store') }}">
    @csrf
    <h2 class="h2" style="margin-top:0">New CHO staff account</h2>
    <div class="field"><label for="name">Name</label><input id="name" type="text" name="name" value="{{ old('name') }}" required>@error('name')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="password">Password for this account (8 characters or more)</label><input id="password" type="password" name="password" minlength="8" required>@error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <button class="btn btn-primary">Create account</button>
  </form>
</div>

<dialog class="modal" id="addDialog">
  <div class="modal-top"></div>
  <div class="modal-body">
    <h2>Confirm new account</h2>
    <p>Enter your admin password to create this CHO staff account.</p>
    <div class="field">
      <label for="addPw">Your admin password</label>
      <input id="addPw" type="password" name="admin_password" form="createForm" autocomplete="current-password">
      @error('admin_password')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost js-cancel">Cancel</button>
      <button type="submit" class="btn btn-primary" form="createForm">Create account</button>
    </div>
  </div>
</dialog>

<dialog class="modal danger" id="delDialog">
  <div class="modal-top"></div>
  <div class="modal-body">
    <h2>Remove account</h2>
    <p id="delText">Enter your admin password to continue.</p>
    <form id="deleteForm" method="POST">
      @csrf
      @method('DELETE')
    </form>
    <div class="field">
      <label for="delPw">Your admin password</label>
      <input id="delPw" type="password" name="admin_password" form="deleteForm" autocomplete="current-password">
      <div class="err" id="delErr">@error('admin_password'){{ $message }}@enderror</div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost js-cancel">Cancel</button>
      <button type="submit" class="btn btn-danger" form="deleteForm">Remove account</button>
    </div>
  </div>
</dialog>
@endsection

@push('scripts')
<script>
const createForm = document.getElementById('createForm');
const addDialog = document.getElementById('addDialog');
const delDialog = document.getElementById('delDialog');
const deleteForm = document.getElementById('deleteForm');
const addPw = document.getElementById('addPw');
const delPw = document.getElementById('delPw');

createForm.addEventListener('submit', function (e) {
  if (!addPw.value) {
    e.preventDefault();
    if (createForm.reportValidity()) { addDialog.showModal(); addPw.focus(); }
  }
});

deleteForm.addEventListener('submit', function (e) {
  if (!delPw.value) {
    e.preventDefault();
    document.getElementById('delErr').textContent = 'Please enter your admin password.';
    delPw.focus();
  }
});

function openDelete(btn) {
  deleteForm.action = btn.dataset.action;
  document.getElementById('delText').textContent =
    'This removes ' + btn.dataset.name + ' for good. They will no longer be able to log in. Enter your admin password to confirm.';
  delPw.value = '';
  delDialog.showModal();
  delPw.focus();
}

document.querySelectorAll('.js-delete').forEach(function (b) {
  b.addEventListener('click', function () {
    document.getElementById('delErr').textContent = '';
    openDelete(b);
  });
});

document.querySelectorAll('.js-cancel').forEach(function (b) {
  b.addEventListener('click', function () {
    var d = b.closest('dialog');
    d.querySelector('input[type=password]').value = '';
    d.close();
  });
});

const reopen = @json(session('reopen'));
const reopenId = @json(session('reopen_id'));
if (reopen === 'add') { addDialog.showModal(); addPw.focus(); }
if (reopen === 'delete') {
  var target = document.querySelector('.js-delete[data-id="' + reopenId + '"]');
  if (target) { openDelete(target); document.getElementById('delErr').textContent = @json($errors->first('admin_password')); }
}
</script>
@endpush
