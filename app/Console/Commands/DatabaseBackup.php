<?php

namespace App\Console\Commands;

use App\Services\Settings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

#[Signature('backup:database')]
#[Description('Buat backup database ke storage/app/backups sesuai pengaturan')]
class DatabaseBackup extends Command
{
    public function handle(): int
    {
        $settings = app(Settings::class);
        $backup = $settings->get('backup', Settings::defaults()['backup']);

        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');

        $connection = DB::connection();
        $tables = array_column(Schema::getTables($this->schemaName()), 'name');

        $sql = '-- Backup '.config('database.default').' : '.now()->toDateTimeString()."\n"
            ."-- Dibuat oleh SIMHAK WBP\n\n";

        foreach ($tables as $table) {
            try {
                $sql .= $this->dumpTable($connection, $table);
            } catch (\Throwable $exception) {
                $this->warn("Melewati tabel {$table}: ".$exception->getMessage());
            }
        }

        $filename = 'backups/backup-'.now()->format('Y-m-d-H-i-s').'.sql';
        $disk->put($filename, $sql);

        $this->info("Backup tersimpan: {$filename} (".number_format(strlen($sql)).' bytes).');

        if (! empty($backup['api_url'])) {
            $this->pushRemote($disk, $filename, $backup);
        }

        return self::SUCCESS;
    }

    /**
     * Nama skema aktif: MySQL memerlukan database eksplisit agar tabel
     * dari database lain yang terlihat oleh user tidak ikut ter-backup.
     */
    private function schemaName(): ?string
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? DB::connection()->getDatabaseName()
            : null;
    }

    /**
     * Buat DDL (MySQL) dan seluruh baris INSERT untuk satu tabel.
     */
    private function dumpTable(Connection $connection, string $table): string
    {
        $sql = "-- Tabel: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

        if ($connection->getDriverName() === 'mysql') {
            $create = DB::select("SHOW CREATE TABLE `{$table}`");

            $sql .= $create[0]->{'Create Table'}.";\n\n";
        } else {
            $sql .= "\n";
        }

        $rows = DB::table($table)->get();

        if ($rows->isNotEmpty()) {
            $columns = array_keys((array) $rows->first());
            $columnList = implode(', ', array_map(fn (string $column) => "`{$column}`", $columns));

            foreach ($rows as $row) {
                $values = array_map(
                    fn (mixed $value) => $value === null
                        ? 'NULL'
                        : $connection->getPdo()->quote((string) $value),
                    (array) $row,
                );

                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES (".implode(', ', $values).");\n";
            }
        }

        return $sql."\n";
    }

    /**
     * Kirim file backup ke API eksternal bila dikonfigurasi.
     */
    private function pushRemote(Filesystem $disk, string $filename, array $backup): void
    {
        try {
            $request = Http::retry(3, 500)
                ->timeout(30)
                ->connectTimeout(10);

            if (! empty($backup['api_key'])) {
                $request = $request->withToken($backup['api_key']);
            }

            $response = $request->attach(
                'backup',
                $disk->get($filename),
                basename($filename),
            )->post($backup['api_url']);

            if ($response->successful()) {
                $this->info('Backup berhasil dikirim ke API eksternal.');

                return;
            }

            $this->warn("Gagal mengirim backup ke API eksternal (status {$response->status()}).");
        } catch (\Throwable $exception) {
            $this->warn('Gagal mengirim backup ke API eksternal: '.$exception->getMessage());
        }
    }
}
