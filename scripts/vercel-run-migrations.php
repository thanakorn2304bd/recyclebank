<?php

declare(strict_types=1);

$deploymentEnvironment = getenv('VERCEL_TARGET_ENV') ?: getenv('VERCEL_ENV') ?: 'unknown';

if ($deploymentEnvironment !== 'production') {
    fwrite(STDOUT, "Skipping database migrations for Vercel environment [{$deploymentEnvironment}].".PHP_EOL);

    exit(0);
}

if (! getenv('DB_HOST') && ! getenv('TIDB_HOST')) {
    fwrite(STDERR, 'Production deployment is missing database host configuration for migrations.'.PHP_EOL);

    exit(1);
}

fwrite(STDOUT, 'Running Laravel database migrations for the production deployment...'.PHP_EOL);

$command = escapeshellarg(PHP_BINARY).' artisan migrate --force';
passthru($command, $exitCode);

exit($exitCode);
