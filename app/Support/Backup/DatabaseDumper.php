<?php

namespace App\Support\Backup;

use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseDumper
{
    public function dump(): string
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $databaseName = DB::connection()->getDatabaseName();

        $output = "-- RecycleBank PHP database dump\n";
        $output .= '-- Database: '.$databaseName."\n";
        $output .= '-- Generated: '.now()->format('Y-m-d H:i:s')."\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET NAMES utf8mb4;\n\n";

        $tables = $pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')
            ->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $output .= $this->dumpTable($pdo, $table);
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $output;
    }

    private function dumpTable(PDO $pdo, string $table): string
    {
        $quoted = '`'.str_replace('`', '``', $table).'`';

        $output = "\n-- --------------------------------------------------------\n";
        $output .= "-- Table structure for {$quoted}\n";
        $output .= "-- --------------------------------------------------------\n";
        $output .= "DROP TABLE IF EXISTS {$quoted};\n";

        $create = $pdo->query("SHOW CREATE TABLE {$quoted}")->fetch(PDO::FETCH_ASSOC);
        $output .= ($create['Create Table'] ?? '').";\n\n";

        $rows = $pdo->query("SELECT * FROM {$quoted}", PDO::FETCH_ASSOC);
        $columns = null;
        $buffer = [];
        $batchSize = 200;

        foreach ($rows as $row) {
            if ($columns === null) {
                $columns = array_map(fn ($c) => '`'.str_replace('`', '``', (string) $c).'`', array_keys($row));
            }

            $values = array_map(fn ($v) => $this->quoteValue($pdo, $v), array_values($row));
            $buffer[] = '('.implode(',', $values).')';

            if (count($buffer) >= $batchSize) {
                $output .= $this->flushInsert($quoted, $columns, $buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            $output .= $this->flushInsert($quoted, $columns ?? [], $buffer);
        }

        return $output."\n";
    }

    private function flushInsert(string $quotedTable, array $columns, array $rows): string
    {
        return 'INSERT INTO '.$quotedTable.' ('.implode(',', $columns).") VALUES\n"
            .implode(",\n", $rows).";\n";
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    public function restore(string $sql): void
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->splitStatements($sql) as $statement) {
            $trimmed = trim($statement);

            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $pdo->exec($trimmed);
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    private function splitStatements(string $sql): iterable
    {
        $length = strlen($sql);
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && $prev !== '\\' && ! $inDouble && ! $inBacktick) {
                $inSingle = ! $inSingle;
            } elseif ($char === '"' && $prev !== '\\' && ! $inSingle && ! $inBacktick) {
                $inDouble = ! $inDouble;
            } elseif ($char === '`' && ! $inSingle && ! $inDouble) {
                $inBacktick = ! $inBacktick;
            }

            if ($char === ';' && ! $inSingle && ! $inDouble && ! $inBacktick) {
                yield $buffer;
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            yield $buffer;
        }
    }
}
