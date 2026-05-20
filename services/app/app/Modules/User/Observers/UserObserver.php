<?php

declare(strict_types=1);

namespace App\Modules\User\Observers;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function saved(User $user): void
    {
        $this->invalidate($user);
    }

    public function deleted(User $user): void
    {
        $this->invalidate($user);
    }

    private function invalidate(User $user): void
    {
        Cache::forget("user:{$user->id}");
        Cache::forget('user:email:'.md5($user->email));
    }
}
