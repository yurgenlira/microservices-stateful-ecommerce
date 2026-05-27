<?php

declare(strict_types=1);

use App\Modules\User\Database\Factories\UserFactory;
use App\Modules\User\PublicApi\UserDto;
use App\Modules\User\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new UserService;
});

it('returns a UserDto when user exists by id', function () {
    $user = UserFactory::new()->create();

    $dto = $this->service->findById($user->id);

    expect($dto)->toBeInstanceOf(UserDto::class)
        ->and($dto->id)->toBe($user->id)
        ->and($dto->name)->toBe($user->name)
        ->and($dto->email)->toBe($user->email);
});

it('returns null when user does not exist by id', function () {
    $dto = $this->service->findById(9999);

    expect($dto)->toBeNull();
});

it('returns a UserDto when user exists by email', function () {
    $user = UserFactory::new()->create(['email' => 'test@example.com']);

    $dto = $this->service->findByEmail('test@example.com');

    expect($dto)->toBeInstanceOf(UserDto::class)
        ->and($dto->id)->toBe($user->id)
        ->and($dto->email)->toBe('test@example.com');
});

it('returns null when user does not exist by email', function () {
    $dto = $this->service->findByEmail('nobody@example.com');

    expect($dto)->toBeNull();
});

it('maps createdAt as DateTimeImmutable', function () {
    $user = UserFactory::new()->create();

    $dto = $this->service->findById($user->id);

    expect($dto->createdAt)->toBeInstanceOf(DateTimeImmutable::class);
});
