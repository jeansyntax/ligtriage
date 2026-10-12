<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Queue') | LIGTriage</title>
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/ligtriage.css') }}">
<script>try{document.documentElement.dataset.theme=localStorage.getItem('lt-theme')||''}catch(e){}</script>
</head>
<body>
@php($isAdmin = auth()->user()->role === 'admin')
<header class="site-head">
  <div class="head-in">
    <a class="brand2" href="{{ route($isAdmin ? 'staff.summary' : 'staff.queue') }}">
      <img src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo">
      <span class="wm"><b>LIGTriage</b><small>Triage Queue · City Health Office, San Fernando City, La Union</small></span>
    </a>
    <div class="online"><i></i>System Online · {{ now()->format('M d, Y') }}</div>
  </div>
  <div class="gradline"></div>
</header>

<nav class="subnav">
  <div class="sub-in">
    <div class="links">
      @if(! $isAdmin)
      <a href="{{ route('staff.queue') }}" class="{{ request()->routeIs('staff.queue*') ? 'on' : '' }}">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>Queue</a>
      <a href="{{ route('staff.create') }}" class="{{ request()->routeIs('staff.create') ? 'on' : '' }}">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>Add patient</a>
      @endif
      <a href="{{ route('staff.summary') }}" class="{{ request()->routeIs('staff.summary') ? 'on' : '' }}">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M8 17V9M13 17V5M18 17v-6"/></svg>Daily summary</a>
      <a href="{{ route('entrance.show') }}" target="_blank" rel="noopener">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>Entrance screen</a>
      @if($isAdmin)
      <a href="{{ route('staff.users') }}" class="{{ request()->routeIs('staff.users*') ? 'on' : '' }}">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>CHO staff</a>
      @endif
    </div>

    <details class="menu">
      <summary class="menu-btn">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Menu
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
      </summary>
      <div class="menu-panel">
        <span class="who">{{ auth()->user()->name }}</span>
        <button type="button" id="themeBtn">Dark mode</button>
        <form method="POST" action="{{ route('logout') }}">@csrf<button>Log out</button></form>
      </div>
    </details>
  </div>
</nav>

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
  document.addEventListener('click',function(e){
    var m=document.querySelector('.menu');
    if(m&&!m.contains(e.target))m.removeAttribute('open');
  });
})();
</script>
@stack('scripts')
</body>
</html>
