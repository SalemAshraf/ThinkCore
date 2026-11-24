# ThinkCore

ThinkCore is an independent course marketplace in progress, with student, instructor and administrator accounts. It is not the teacher-branded subscription platform.

## Current milestone

The November 2026 maintenance pass updates compatible dependencies, repairs authentication and asset builds, and secures the existing course-draft workflow. It does **not** make the marketplace production-ready.

- Laravel 12.73.0, Sanctum 4.5.0, compatible PHP 8.2 dependency resolution.
- Instructor course drafts are scoped to their owner for listing, reading and updates.
- Draft details and metadata save with validated category, language, level and prices.
- Arabic and duplicate titles work without colliding slugs. Editing preserves URLs.
- New instructor-application documents use private storage and authenticated administrator downloads. Public file-manager routes are disabled until media isolation is implemented.
- Email verification, profile callbacks, Linux view names, API controller namespaces and Vite entry points are repaired.

## Local setup

Use PHP 8.2+ with the normal Laravel extensions, Composer, Node 22.12+ and npm. The current lock targets PHP 8.2 compatibility. SQLite is sufficient for tests; verify MySQL separately before production.

```sh
composer install
cp .env.example .env
php artisan key:generate
# Configure a local database and mail driver in .env first.
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Do not use demo seeders or test accounts in production. Set `APP_DEBUG=false` on any shared environment. Never commit `.env`, keys, databases or customer uploads.

## Verification

```sh
php artisan test
npm run build
composer audit
npm audit
php artisan route:cache
php artisan route:clear
```

The first maintenance milestone passed 32 automated tests with 92 assertions, a production asset build and route caching. Both dependency audits reported no known advisories at the check time. This is not a guarantee against undiscovered vulnerabilities. Browser checks cover login and the instructor draft form on desktop and mobile; the existing marketing template still needs content and design work.

## Remaining product work

1. Course curriculum: sections, ordered lessons, attachments, validation, instructor ownership and safe draft deletion.
2. Administrator review and publishing, followed by a database-backed course catalog, search and public course details. Existing marketing sections contain template/sample content, not verified marketplace activity.
3. Student enrollment, free-course access, lesson progress and protected delivery. Never accept a browser-supplied enrollment or price as authority.
4. Payment-provider selection, signed idempotent webhooks, orders, refunds and instructor settlement. No real payments are implemented or authorized by this maintenance pass.
5. Private video/attachment storage and entitlement checks. External demo links are public previews only. Do not promise screen-recording prevention.
6. Reviews restricted to enrolled learners, moderation, notifications, queues and operational monitoring.
7. Production readiness: backup/restore, MySQL tests, rate limits, file and API permissions, accessibility, responsive templates, privacy/terms and recovery flows.

## Upgrade and deployment notes

Laravel 12 security support is listed through February 24, 2027: https://laravel.com/docs/12.x/releases . Plan the Laravel 13 migration separately using https://laravel.com/docs/13.x/upgrade . Current Debugbar, Tinker and Pest constraints need coordinated changes; do not just change the framework constraint or assume an unknown host has the required PHP version.

No hosting or domain was declared in repository metadata during the audit. This pass does not deploy anything and makes no production-data migration.

Before any deployment, privately audit legacy `users.document` values and files already in `public/uploads`. Old documents may be publicly served by the old code. New downloads intentionally do not serve legacy arbitrary paths. Make a verified backup, move only confirmed documents into private storage, update their records and remove public copies through a reviewed migration. Changing the storage root also requires preserving existing public asset paths. Never run a destructive migration to make a deployment pass.
