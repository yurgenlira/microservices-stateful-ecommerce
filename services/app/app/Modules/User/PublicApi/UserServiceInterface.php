<?php

declare(strict_types=1);

namespace App\Modules\User\PublicApi;

interface UserServiceInterface
{
    public function findById(int $id): ?UserDto;

    public function findByEmail(string $email): ?UserDto;
}
