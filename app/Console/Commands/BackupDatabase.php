<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * მონაცემთა ბაზის სარეზერვო ასლი → storage/app/backups/kidsmart-YYYY-MM-DD_HHMMSS.sql.gz
 * სუფთა PHP-ით (mysqldump-ის გარეშე — Windows-ზეც მუშაობს). ინახება ბოლო N ასლი.
 *   php artisan ks:backup            — ახლავე
 *   php artisan ks:backup --keep=30  — 30 ასლის შენახვა
 * აღდგენა: გახსენი .gz, მიიღებ .sql ფაილს → phpMyAdmin „Import“ ან `mysql kidsmart < file.sql`.
 */
class BackupDatabase extends Command
{
    protected $signature = 'ks:backup {--keep=14 : რამდენი ბოლო ასლი შევინახოთ}';
    protected $description = 'მონაცემთა ბაზის სარეზერვო ასლი (gzip) storage/app/backups-ში';

    public const DIR = 'backups';

    public function handle(): int
    {
        $dir = storage_path('app/' . self::DIR);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("საქაღალდე ვერ შეიქმნა: {$dir}");
            return self::FAILURE;
        }

        $conn   = DB::connection();
        $driver = $conn->getDriverName();
        $stamp  = now()->format('Y-m-d_His');

        try {
            if ($driver === 'sqlite') {
                $path = "{$dir}/kidsmart-{$stamp}.sqlite.gz";
                $this->gzipFile($conn->getDatabaseName(), $path);
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                $path = "{$dir}/kidsmart-{$stamp}.sql.gz";
                $this->dumpMysql($path);
            } else {
                $this->error("ბაზის ტიპი „{$driver}“ არ არის მხარდაჭერილი.");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            if (isset($path) && is_file($path)) @unlink($path);
            $this->error('სარეზერვო ასლი ვერ შეიქმნა: ' . $e->getMessage());
            report($e);
            return self::FAILURE;
        }

        $this->info('შეიქმნა: ' . basename($path) . ' (' . round(filesize($path) / 1024) . ' KB)');
        $this->prune($dir, max(1, (int) $this->option('keep')));
        return self::SUCCESS;
    }

    private function dumpMysql(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $gz  = gzopen($path, 'wb6');
        if (! $gz) throw new \RuntimeException('ფაილი ვერ გაიხსნა ჩასაწერად.');

        $w = fn (string $s) => gzwrite($gz, $s);
        $w("-- KidSmart backup " . now()->toDateTimeString() . "\n");
        $w("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tables = [];
        foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(\PDO::FETCH_NUM) as $row) {
            if (($row[1] ?? 'BASE TABLE') === 'BASE TABLE') $tables[] = $row[0];
        }

        foreach ($tables as $t) {
            $q = '`' . str_replace('`', '``', $t) . '`';
            $create = $pdo->query("SHOW CREATE TABLE {$q}")->fetch(\PDO::FETCH_NUM)[1];
            $w("DROP TABLE IF EXISTS {$q};\n{$create};\n\n");

            $offset = 0;
            $chunk  = 500;
            do {
                $rows = $pdo->query("SELECT * FROM {$q} LIMIT {$chunk} OFFSET {$offset}")->fetchAll(\PDO::FETCH_ASSOC);
                if ($rows) {
                    $cols = '(' . implode(',', array_map(fn ($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($rows[0]))) . ')';
                    $vals = [];
                    foreach ($rows as $r) {
                        $vals[] = '(' . implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $r)) . ')';
                    }
                    $w("INSERT INTO {$q} {$cols} VALUES\n" . implode(",\n", $vals) . ";\n");
                }
                $offset += $chunk;
            } while (count($rows) === $chunk);
            $w("\n");
        }

        $w("SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
    }

    private function gzipFile(string $src, string $path): void
    {
        $in = fopen($src, 'rb');
        $gz = gzopen($path, 'wb6');
        if (! $in || ! $gz) throw new \RuntimeException('ფაილი ვერ გაიხსნა.');
        while (! feof($in)) gzwrite($gz, fread($in, 1 << 20));
        fclose($in);
        gzclose($gz);
    }

    private function prune(string $dir, int $keep): void
    {
        $files = glob("{$dir}/kidsmart-*.gz") ?: [];
        rsort($files);   // სახელში თარიღია — ახლები პირველები
        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
            $this->line('წაიშალა ძველი: ' . basename($old));
        }
    }
}
