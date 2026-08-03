---
description: Create Laravel database migrations using this project's file naming convention (YYYY_MM_DD_NNNNNN_description.php with a clean incrementing sequence instead of a raw HHMMSS timestamp). Use this whenever the user asks to create, add, or write a new database migration, add a column, create a table, or make any schema change in this Laravel project.
when_to_use: Creating or generating a new database migration file in database/migrations
---

# Laravel migration naming convention

This project names new migration files like `2026_01_01_000001_create_jobs_table.php`:

`{today's date as Y_m_d}_{6-digit sequence, zero-padded}_{snake_case_description}.php`

The date segment is the **real current date**, not a placeholder. The sequence segment increments
cleanly per day (`000001`, `000002`, ...) instead of using the real time-of-day (`HHMMSS`) that
`php artisan make:migration` generates by default.

## Steps to create a migration

1. Get today's date: `date +%Y_%m_%d`.
2. List `database/migrations/` and find any existing files that start with that exact date prefix.
   Extract their 6-digit sequence segment (the number right after the date, before the description)
   and take the max. If none exist for today, start at `1`.
3. Zero-pad the next sequence number to 6 digits (e.g. `1` -> `000001`).
4. Convert the migration's purpose into `snake_case` for the description part
   (e.g. "create jobs table" -> `create_jobs_table`, "add role to telegram users table" ->
   `add_role_to_telegram_users_table`).
5. Filename: `database/migrations/{date}_{sequence}_{description}.php`.
6. Write the migration using the anonymous-class style already used throughout this repo
   (`return new class extends Migration { public function up(): void {...} public function down(): void {...} };`),
   matching the `Schema::create` / `Schema::table` / `Schema::dropIfExists` patterns seen in
   `database/migrations/*.php`.

## Example

Existing files for today (`2026_08_02`):
```
2026_08_02_000001_create_orders_table.php
2026_08_02_000002_add_status_to_orders_table.php
```

Next migration ("add index to orders table") becomes:
```
2026_08_02_000003_add_index_to_orders_table.php
```

If no migration exists yet for today's date, the first one starts at `000001`, e.g.:
```
2026_08_03_000001_create_refunds_table.php
```

Do not use Laravel's default `Y_m_d_His` (seconds-based) timestamp prefix for new migrations in
this project — always use the incrementing sequence described above.
