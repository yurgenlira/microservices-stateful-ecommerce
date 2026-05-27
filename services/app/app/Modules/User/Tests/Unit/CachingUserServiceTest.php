<?php

declare(strict_types=1);

use App\Modules\User\PublicApi\UserDto;
use App\Modules\User\PublicApi\UserServiceInterface;
use App\Modules\User\Services\CachingUserService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->inner = Mockery::mock(UserServiceInterface::class);
    $this->service = new CachingUserService($this->inner);
});

it('calls Cache::remember with key "user:{id}" and delegates to inner', function () {
    $dto = new UserDto(id: 1, name: 'Alice', email: 'alice@example.com', createdAt: new DateTimeImmutable);
    $this->inner->expects('findById')->once()->with(1)->andReturn($dto);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'user:1')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findById(1);

    expect($result)->toBe($dto);
});

it('returns null when inner findById returns null', function () {
    $this->inner->expects('findById')->once()->with(99)->andReturn(null);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findById(99);

    expect($result)->toBeNull();
});

it('calls Cache::remember with hashed key for findByEmail and delegates to inner', function () {
    $email = 'alice@example.com';
    $dto = new UserDto(id: 1, name: 'Alice', email: $email, createdAt: new DateTimeImmutable);
    $this->inner->expects('findByEmail')->once()->with($email)->andReturn($dto);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'user:email:'.md5($email))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findByEmail($email);

    expect($result)->toBe($dto);
});

it('uses separate cache keys for different user ids', function () {
    $dto1 = new UserDto(id: 1, name: 'Alice', email: 'alice@example.com', createdAt: new DateTimeImmutable);
    $dto2 = new UserDto(id: 2, name: 'Bob', email: 'bob@example.com', createdAt: new DateTimeImmutable);

    $this->inner->expects('findById')->once()->with(1)->andReturn($dto1);
    $this->inner->expects('findById')->once()->with(2)->andReturn($dto2);

    Cache::shouldReceive('remember')
        ->twice()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->findById(1))->toBe($dto1)
        ->and($this->service->findById(2))->toBe($dto2);
});
