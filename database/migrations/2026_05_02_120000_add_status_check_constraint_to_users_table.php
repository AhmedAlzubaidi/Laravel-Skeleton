<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // No-op on SQLite: adding a CHECK to an existing table requires a rebuild,
    // and the Eloquent cast already guards application writes there.
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $values = collect(UserStatus::values())
            ->map(fn (string $v): string => "'".$v."'")
            ->implode(', ');

        match ($driver) {
            'pgsql', 'mysql', 'mariadb' => DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ({$values}))"),
            default                     => null,
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
