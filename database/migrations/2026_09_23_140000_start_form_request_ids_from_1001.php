<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Номера заявок (и externalId leadpop-N в CRM) начинаются с 1001.
 * Уже существующие заявки сохраняют свои id; счётчик только поднимается, не опускается.
 */
return new class extends Migration
{
    private const START = 1001;

    public function up(): void
    {
        $next = max(self::START, (int) DB::table('form_requests')->max('id') + 1);

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE form_requests AUTO_INCREMENT = ' . $next),
            'sqlite' => $this->sqliteSetSequence($next - 1),
            'pgsql' => DB::statement("SELECT setval(pg_get_serial_sequence('form_requests', 'id'), {$next}, false)"),
        };
    }

    public function down(): void
    {
        // Откатывать счётчик назад небезопасно — оставляем как есть.
    }

    private function sqliteSetSequence(int $seq): void
    {
        DB::table('sqlite_sequence')->where('name', 'form_requests')->delete();
        DB::table('sqlite_sequence')->insert(['name' => 'form_requests', 'seq' => $seq]);
    }
};
