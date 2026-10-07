<?php

namespace App;

enum UserRole: string
{
    case User = 'user';
    case Instructor = 'instructor';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Instructor => 'Instructor',
            self::Admin => 'Administrador',
        };
    }
}
