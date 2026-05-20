<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\User\PublicApi\UserDto;
use App\Modules\User\PublicApi\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function findById(int $id): ?UserDto
    {
        $user = User::find($id);

        return $user ? $this->toDto($user) : null;
    }

    public function findByEmail(string $email): ?UserDto
    {
        $user = User::where('email', $email)->first();

        return $user ? $this->toDto($user) : null;
    }

    private function toDto(User $user): UserDto
    {
        return new UserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            createdAt: new \DateTimeImmutable($user->created_at->toDateTimeString()),
        );
    }
}
