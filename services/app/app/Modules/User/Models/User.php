<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Modules\User\Database\Factories\UserFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use SoftDeletes;

    protected static string $factory = UserFactory::class;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
