# RecycleBank

Laravel 12 application for managing a community recycling bank. The system supports:

- household self-registration with approval flow
- staff/admin account management
- deposit and withdraw transactions
- material and price management
- reports with PDF and Excel export

## Stack

- PHP 8.4+
- Laravel 12
- MySQL 8 via Laravel Sail
- Redis
- DomPDF and PhpSpreadsheet

## Local Setup

1. Install dependencies.

```bash
composer install
npm install
```

2. Create your environment file.

```bash
cp .env.example .env
php artisan key:generate
```

3. Start the Sail services.

```bash
./vendor/bin/sail up -d
```

4. Run migrations and seeders.

```bash
./vendor/bin/sail artisan migrate --seed
```

5. Start the frontend dev server when needed.

```bash
npm run dev
```

## Testing

The test suite is configured to use the Sail MySQL `testing` database through the forwarded port on `127.0.0.1:3306`.

Run tests from the host:

```bash
php artisan test
```

Or run them inside Sail:

```bash
./vendor/bin/sail test
```

## Code Quality

Format code with Pint:

```bash
./vendor/bin/pint
```

## Vercel / TiDB Deploys

Production deploys on Vercel run the Composer `vercel` script. This project now uses that hook to run `php artisan migrate --force` automatically only when the deployment environment is `production`.

If production TiDB is already behind on schema changes, trigger a new production deployment after pulling this change, or run the migration manually in an environment that has the production `TIDB_*` or `DB_*` variables:

```bash
php artisan migrate --force
```

## Default Accounts

Seeders create example accounts for local development:

- `admin`
- `staff`
- `member`

Use the seeded passwords defined in the seeders for local testing.
