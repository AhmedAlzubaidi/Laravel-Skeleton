<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\DTOs\UserDto;
use App\Enums\UserStatus;
use App\Queries\GetUsersQuery;
use Illuminate\Http\JsonResponse;
use App\Commands\CreateUserCommand;
use App\Commands\UpdateUserCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;

final readonly class UserController
{
    public function index(GetUsersQuery $query): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->when($query->username, fn (Builder $q, string $username): Builder => $q->where('username', 'like', "%{$username}%"))
            ->when($query->email, fn (Builder $q, string $email): Builder => $q->where('email', 'like', "%{$email}%"))
            ->when($query->status, fn (Builder $q, UserStatus $status): Builder => $q->where('status', $status))
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return response()->json([
            ...UserDto::collect($users)->toArray(),
            'message' => __('Users fetched successfully'),
        ]);
    }

    public function store(CreateUserCommand $command): JsonResponse
    {
        Gate::authorize('create', User::class);
        $user = User::create($command->validated());

        return response()->json([
            'data'    => UserDto::from($user),
            'message' => __('User created successfully'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('view', $user);

        return response()->json([
            'data'    => UserDto::from($user),
            'message' => __('User fetched successfully'),
        ]);
    }

    public function update(UpdateUserCommand $command, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('update', $user);

        if ($command->status instanceof UserStatus && $command->status !== $user->status) {
            Gate::authorize('updateStatus', $user);
        }

        $user->update($command->validated());

        return response()->json([
            'data'    => UserDto::from($user),
            'message' => __('User updated successfully'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => __('User deleted successfully'),
        ]);
    }
}
