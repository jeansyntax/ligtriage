<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

# staff accounts, admin only
class UserController extends Controller
{
    public function index()
    {
        $this->adminOnly();

        return view('staff.users', ['users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,triage_staff',
        ]);

        $user = new User();
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ])->save();

        return back()->with('ok', 'Account created.');
    }

    public function destroy(User $user)
    {
        $this->adminOnly();

        if ($user->id === Auth::id()) {
            return back()->with('ok', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('ok', 'Account removed.');
    }

    private function adminOnly(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }
}
