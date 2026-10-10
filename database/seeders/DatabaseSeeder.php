<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'CHO Admin', 'email' => 'admin@ligtriage.test', 'role' => 'admin'],
            ['name' => 'Triage Nurse', 'email' => 'staff@ligtriage.test', 'role' => 'triage_staff'],
        ];

        foreach ($accounts as $a) {
            $user = User::firstOrNew(['email' => $a['email']]);
            $user->forceFill([
                'name' => $a['name'],
                'role' => $a['role'],
                'password' => Hash::make('password'),
            ])->save();
        }
    }
}
