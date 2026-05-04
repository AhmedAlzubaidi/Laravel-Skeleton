<?php

declare(strict_types=1);

namespace App\Console\Commands\Users;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;

#[Signature('users:audit-statuses')]
#[Description('Scan the users table for rows whose status value is not a known UserStatus case.')]
final class AuditStatusesCommand extends Command
{
    public function handle(): int
    {
        $invalid = User::query()
            ->whereNotIn('status', UserStatus::values())
            ->get(['id', 'username', 'status']);

        if ($invalid->isEmpty()) {
            $this->info('All user statuses are valid.');

            return self::SUCCESS;
        }

        $this->error(sprintf('Found %d user(s) with invalid status:', $invalid->count()));
        $this->newLine();
        $this->table(
            ['id', 'username', 'status'],
            $invalid->map(fn (User $user): array => [
                $user->id,
                $user->username,
                $user->getRawOriginal('status'),
            ])->all(),
        );

        return self::FAILURE;
    }
}
