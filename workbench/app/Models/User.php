<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as FoundationUser;

class User extends FoundationUser
{
    use HasFactory;
}
