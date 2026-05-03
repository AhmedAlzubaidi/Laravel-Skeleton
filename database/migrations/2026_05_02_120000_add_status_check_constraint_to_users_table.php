<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add a database-level constraint guaranteeing users.status only ever
     * holds a value from the UserStatus enum, even if a row is inserted
     * outside Eloquent (raw SQL, console, another service).
     *
     * No-op on SQLite (in-memory test DB) since adding constraints to an
     * existing SQLite table requires a full rebuild and the runtime cast
     * already protects application code paths.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $values = collect(UserStatus::values())
            ->map(fn (string $v): string => "'".$v."'")
            ->implode(', ');

        match ($driver) {
            'pgsql'            => DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ({$values}))"),
            'mysql', 'mariadb' => DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ({$values}))"),
            default            => null,
        };
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        match ($driver) {
            'pgsql', 'mysql', 'mariadb' => DB::statement('ALTER TABLE users DROP CONSTRAINT users_status_check'),
            default                     => null,
        };
    }
};
