<?php

declare(strict_types=1);

namespace App\Modules\User\PublicApi;

readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public \DateTimeImmutable $createdAt,
    ) {}
}
