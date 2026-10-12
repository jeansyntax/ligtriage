<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

# CHO staff accounts. admin only, and every add or remove needs the admin password
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
            'admin_password' => 'required|string',
        ]);

        if (! $this->passwordOk($data['admin_password'])) {
            return back()
                ->withInput($request->except('password', 'admin_password'))
                ->withErrors(['admin_password' => 'Your admin password is not correct.'])
                ->with('reopen', 'add');
        }

        # only CHO staff can be created here
        $user = new User();
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'triage_staff',
            'password' => Hash::make($data['password']),
        ])->save();

        return back()->with('ok', 'CHO staff account created.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin' || $user->id === Auth::id()) {
            return back()->with('ok', 'Admin accounts cannot be removed here.');
        }

        $request->validate(['admin_password' => 'required|string']);

        if (! $this->passwordOk($request->input('admin_password'))) {
            return back()
                ->withErrors(['admin_password' => 'Your admin password is not correct.'])
                ->with('reopen', 'delete')
                ->with('reopen_id', $user->id);
        }

        $user->delete();

        return back()->with('ok', 'CHO staff account removed.');
    }

    private function adminOnly(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function passwordOk(string $plain): bool
    {
        return Hash::check($plain, Auth::user()->password);
    }
}
