<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use Illuminate\Console\Command;

class PromoteAdmin extends Command
{
    protected $signature = 'app:promote-admin {email : Correo de una cuenta existente}';

    protected $description = 'Concede el rol de administrador a una cuenta activa existente';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();
        if ($user === null || $user->status !== 'active') {
            $this->error('No existe una cuenta activa con ese correo.');

            return self::FAILURE;
        }
        $user->role = UserRole::Admin;
        $user->save();
        $this->info('La cuenta ahora es administradora.');

        return self::SUCCESS;
    }
}
