<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Queue') | LIGTriage</title>
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/ligtriage.css') }}">
<script>try{document.documentElement.dataset.theme=localStorage.getItem('lt-theme')||''}catch(e){}</script>
</head>
<body>
<header class="topbar">
  <div class="topbar-in">
    <a class="brand" href="{{ route('staff.queue') }}">
      <span class="logo-chip"><img src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo"></span>
      <span class="wordmark"><b>LIGTriage</b><small>Companion system of LIGTAS</small></span>
    </a>
    <nav>
      <a href="{{ route('staff.queue') }}" class="{{ request()->routeIs('staff.queue*') ? 'on' : '' }}">Queue</a>
      <a href="{{ route('staff.create') }}" class="{{ request()->routeIs('staff.create') ? 'on' : '' }}">Add patient</a>
      <a href="{{ route('staff.summary') }}" class="{{ request()->routeIs('staff.summary') ? 'on' : '' }}">Daily summary</a>
      <a href="{{ route('entrance.show') }}" target="_blank" rel="noopener">Entrance screen</a>
      @if(auth()->user()->role === 'admin')
        <a href="{{ route('staff.users') }}" class="{{ request()->routeIs('staff.users*') ? 'on' : '' }}">Staff accounts</a>
      @endif
    </nav>
    <div class="tools">
      <button type="button" class="link-btn" id="themeBtn" aria-label="Switch light or dark mode">Dark mode</button>
      <span>{{ auth()->user()->name }}</span>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-btn">Log out</button></form>
    </div>
  </div>
</header>

<main class="container">
  @if(session('ok'))<div class="alert alert-ok">{{ session('ok') }}</div>@endif
  @yield('content')
</main>

<script>
(function(){
  var b=document.getElementById('themeBtn');
  function label(){b.textContent=document.documentElement.dataset.theme==='dark'?'Light mode':'Dark mode'}
  label();
  b.addEventListener('click',function(){
    var next=document.documentElement.dataset.theme==='dark'?'':'dark';
    document.documentElement.dataset.theme=next;
    try{localStorage.setItem('lt-theme',next)}catch(e){}
    label();
  });
})();
</script>
@stack('scripts')
</body>
</html>
