<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Modules\User\PublicApi\UserDto;
use App\Modules\User\PublicApi\UserServiceInterface;
use Illuminate\Support\Facades\Cache;

class CachingUserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserServiceInterface $inner,
    ) {}

    public function findById(int $id): ?UserDto
    {
        $ttl = config('modules.cache.ttl.user', 300);

        return Cache::remember("user:{$id}", $ttl, fn () => $this->inner->findById($id));
    }

    public function findByEmail(string $email): ?UserDto
    {
        $ttl = config('modules.cache.ttl.user', 300);
        $key = 'user:email:'.md5($email);

        return Cache::remember($key, $ttl, fn () => $this->inner->findByEmail($email));
    }
}
