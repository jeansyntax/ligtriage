<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Staff login | LIGTriage</title>
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/ligtriage.css') }}">
</head>
<body>
<div class="center-page">
  <form class="login-card" method="POST" action="{{ route('login.attempt') }}">
    @csrf
    <img class="logo" src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo">
    <h1>LIGTriage</h1>
    <p class="sub">Triage queue for CHO staff</p>

    <div class="field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
      @error('email')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" required>
    </div>
    <label class="check" style="margin-bottom:1rem">
      <input type="checkbox" name="remember" value="1" checked> Keep me signed in on this device
    </label>
    <button class="btn btn-primary btn-wide">Log in</button>
  </form>
</div>
</body>
</html>
