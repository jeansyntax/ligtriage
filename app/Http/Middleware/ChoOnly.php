<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

# patient queue pages are for CHO triage staff only, never the admin
class ChoOnly
{
    public function handle(Request $request, Closure $next)
    {
        $role = Auth::user()?->role;

        if ($role === 'admin') {
            return redirect()->route('staff.summary')
                ->with('ok', 'The patient queue is only for CHO triage staff.');
        }

        abort_unless($role === 'triage_staff', 403);

        return $next($request);
    }
}
