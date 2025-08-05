<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Commands\CreateUserCommand;
use App\Commands\UpdateUserCommand;
use App\DTOs\UserDto;
use App\Enums\UserStatus;
use App\Models\User;
use App\Queries\GetUsersQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

final readonly class UserController
{
    /**
     * Display a listing of the users.
     */
    public function index(GetUsersQuery $query): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->when($query->name, fn (Builder $q, string $name): Builder => $q->where('name', 'like', "%{$name}%"))
            ->when($query->email, fn (Builder $q, string $email): Builder => $q->where('email', 'like', "%{$email}%"))
            ->when($query->status, fn (Builder $q, UserStatus $status): Builder => $q->where('status', $status))
            ->paginate($query->per_page ?? 10, ['*'], 'page', $query->page ?? 1);

        return response()->json([
            ...UserDto::collect($users)->toArray(),
            'message' => __('Users fetched successfully'),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(CreateUserCommand $command): JsonResponse
    {
        Gate::authorize('create', User::class);
        $commandData = $command->toArray();
        $commandData['password'] = Hash::make($command->password);
        $user = User::create($commandData);

        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User created successfully'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('view', $user);

        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User fetched successfully'),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserCommand $command, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('update', $user);

        $commandData = $command->toArray();

        if ($command->password !== null && $command->password !== '' && $command->password !== '0') {
            $commandData['password'] = Hash::make($command->password);
        } else {
            unset($commandData['password']);
        }

        $user->update($commandData);

        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User updated successfully'),
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json([
            'data' => UserDto::from($user),
            'message' => __('User deleted successfully'),
        ]);
    }
}
