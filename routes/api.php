<?php

declare(strict_types=1);

use App\DTOs\UserDto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:api')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request): JsonResponse {
        return response()->json([
            'data' => UserDto::from($request->user()),
        ]);
    });

    Route::apiResource('users', UserController::class);
});
