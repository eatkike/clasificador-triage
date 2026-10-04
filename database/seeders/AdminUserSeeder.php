<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@triage.local'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('Admin123*'),
                'role' => 'admin',
                'cedula_profesional' => 'ADM-0001',
                'especialidad' => 'Sistemas / Dirección Médica',
                'turno' => 'matutino',
                'is_active' => true,
            ]
        );
    }
}