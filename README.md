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

The first maintenance milestone passed 32 automated tests with 92 assertions, a production asset build and route caching at that maintenance milestone. Both dependency audits reported no known advisories at the check time. This is not a guarantee against undiscovered vulnerabilities. Browser checks cover login and the instructor draft form on desktop and mobile; the existing marketing template still needs content and design work.

## Remaining product work

1. Curriculum follow-up: sections, private attachments and safe draft-course deletion. Ordered text lessons now support owner-scoped create/edit/delete and review resubmission.
2. Administrator review/publishing and a database-backed catalog/search/details now work in the initial text-course flow. The original homepage remains unchanged and contains template/sample content, not verified marketplace activity. The new catalog is at `/courses`, not a replaced homepage.
3. Free-course enrollment, entitlement-checked text lessons, completion and learner library now work. Paid orders, video and attachment delivery remain unimplemented. Never accept a browser-supplied enrollment or price as authority.
4. Payment-provider selection, signed idempotent webhooks, orders, refunds and instructor settlement. No real payments are implemented or authorized by this maintenance pass.
5. Private video/attachment storage and entitlement checks. External demo links are public previews only. Do not promise screen-recording prevention.
6. Reviews restricted to enrolled learners, moderation, notifications, queues and operational monitoring.
7. Production readiness: backup/restore, MySQL tests, rate limits, file and API permissions, accessibility, responsive templates, privacy/terms and recovery flows.

## Upgrade and deployment notes

Laravel 12 security support is listed through February 24, 2027: https://laravel.com/docs/12.x/releases . Plan the Laravel 13 migration separately using https://laravel.com/docs/13.x/upgrade . Current Debugbar, Tinker and Pest constraints need coordinated changes; do not just change the framework constraint or assume an unknown host has the required PHP version.

No hosting or domain was declared in repository metadata during the audit. This pass does not deploy anything and makes no production-data migration.

Before any deployment, privately audit legacy `users.document` values and files already in `public/uploads`. Old documents may be publicly served by the old code. New downloads intentionally do not serve legacy arbitrary paths. Make a verified backup, move only confirmed documents into private storage, update their records and remove public copies through a reviewed migration. Changing the storage root also requires preserving existing public asset paths. Never run a destructive migration to make a deployment pass.

## Free text-course milestone

The additive `/courses` area provides an Arabic RTL catalog, course details, text lessons and learner library. Instructor curriculum routes allow lesson create/edit/delete and submit for review. Administrator review is at `/admin/course-reviews`, under the existing administrator guard. Any course-content change removes publication until review. These new pages are a functional prototype, not a replacement or approval of the entire existing visual identity.

Local checks: 45 tests with 154 assertions passed after the learning-flow hardening. A real browser completed catalog, login, free enrollment, lesson, completion and library (1/1 complete). Only fictional data was used. Paid course access is blocked server-side even if the browser sends a zero price. Lesson content is escaped plain text, not untrusted HTML. Preview lessons are explicitly public; other lesson bodies never appear in the public course-details response.

New learning tables are additive only. Run migrations only on a backed-up, reviewed environment. MySQL concurrency and production migrations remain unverified. No existing host was contacted or changed.

## Curriculum sections and learner reviews

The next additive milestone adds instructor-owned curriculum sections with ordering, renaming and deletion that preserves lessons as unsectioned. Lesson section membership is validated against its own course. Section/content edits return courses to draft pending administrator review.

Enrolled, email-verified learners may submit a 1-5 rating and a text review. Course owners cannot review their own courses. There is one review per learner/course; edits return it to pending moderation. Only administrator-approved reviews appear publicly or count toward the displayed average. Public author labels indicate enrollment, not a verified identity, purchase or course completion. Review text is escaped and learner emails are not published. Administrator moderation is at `/admin/learner-reviews`; a revision check rejects stale approvals after a learner edits a review.

Verification after this slice: 53 tests / 208 assertions pass locally, production assets build, route caching passes, and dependency audits report no known advisories. Actual desktop/mobile browser checks cover section creation, assigning a lesson to a section, learner review submission and the visible pending state. Approved-review display was checked with a synthetic database fixture, while administrator moderation and stale revisions were checked by automated HTTP tests. No production database, customer data, paid provider or hosting deployment was involved. MySQL production migration/concurrency checks remain required before launch.

## Discovery and learning continuation

Catalog filters now combine category, level, language, title search and free-only without changing publication rules. Query values are validated. Learners get previous/next lesson links in section/lesson order, completion state and a library resume link to their first incomplete lesson. Guests viewing previews do not receive links to protected adjacent lessons. Student/instructor sidebars now link to the functional catalog/library, and the administrator sidebar links to both moderation queues.

Local verification: 57 tests / 231 assertions, asset build and route cache pass. Added tests cover ordered course-bound navigation, preview-only links, resume/completion behavior and composed catalog filters with malformed-query rejection. Desktop/mobile catalog, completed library and lesson reading screens were inspected. Multi-lesson navigation is automated-test verified; no hosting deployment or MySQL execution is claimed.

## Reproducible test gates and private-document regression checks

PHP feature tests explicitly disable Vite rendering, so a fresh checkout does not need a generated `public/build/manifest.json` just to run `php artisan test`. This does not test compiled assets: `npm ci && npm run build` remains a separate required gate, followed by browser checks. The local suite was rerun with `public/build` physically absent: 60 tests / 244 assertions passed. A local `.env` and generated application key are still required as setup above describes.

New regression checks prove instructor application documents use private storage, executable uploads are rejected, document download requires the administrator guard, legacy/traversal paths are denied, and the public filemanager remains unreachable. Dormant duplicate API handlers now follow the same private-document rules, but no extra API routes were enabled. This does not migrate or remove any legacy file already hosted publicly; that remains a reviewed deployment task.

## Course publication integrity

Administrator course decisions now include a SHA-256 snapshot of stored course metadata, sections and lesson content. A stale decision is rejected with 409 if the instructor changed/resubmitted the course after the review page opened. The administrator reads the displayed lessons and snapshot in the same transaction. Course updates, section/lesson mutations and review submission lock the parent course row so an approval cannot race past a content change on databases supporting row locks. SQLite regression tests check stale content/metadata/section behavior, but true concurrent MySQL execution is still a pre-launch check, not a claimed local result.

The instructor curriculum page now states draft/pending/published and review status. Verification: 62 tests / 257 assertions pass; actual local Chrome logged in as administrator, opened the review queue and approved a fictional submitted course. The review screen was visually inspected. No live deployment, production migration or real customer data was used.

### Marketplace visual redesign

The root homepage now uses the same approved-course catalog as `/courses`, rather than template inventory. Arabic RTL pages use locally hosted IBM Plex Sans Arabic (SIL Open Font License included under `public/marketplace/fonts`). Original CSS artwork is a neutral placeholder when no permitted local course thumbnail is set. No third-party tracking or image service is required by the marketplace theme.

Landing/catalog, course details, lesson reading, learning library, login/registration and instructor overview share the new visual system. Student dashboard now displays real enrollment/completion data. Instructor overview counts only the signed-in instructor's courses and enrollments; no sample revenue, ratings or student totals are shown. Payments, hosted video, certification issuance and production deployment remain outside this implementation.

Visual inspection used local, explicitly synthetic courses and users. These fixtures and screenshots are not seeded into the application. Automated regressions cover homepage publication filtering, one-third lesson progress, instructor owner isolation, enrollment continuation, escaping and registration paths. Existing instructor editing/profile and administrator management pages retain their prior workflows; this is not a claim that all legacy administration screens were redesigned.

### Instructor studio and account continuation

Native HTML instructor forms now support create, edit and metadata saves with redirects as well as the existing JSON contract. Editing no longer depends on JavaScript intercepting a mismatched form action. Root-level active categories are selectable. Instructor course list/editor/curriculum, account details/password/profile/social forms, recovery/verification screens, course/review moderation and common HTTP error states follow the accepted Arabic theme. Existing role-specific profile GET routes lead to the unified account settings; existing POST routes remain available.

The previously missing existing-student instructor application now accepts a private qualification document and records a pending request for the existing administrator workflow. It cannot change the applicant's role or approval itself. Duplicate pending submissions are rejected; unsuccessful writes clean up new files. No notification email or external document disclosure was added. Instructor accounts owning courses cannot be deleted through self-service, avoiding broken content ownership.

Local real-browser qualification covered native create, metadata save, edit/reload, section/lesson save, submission, administrator approval and resulting catalog visibility, plus profile save/reload and application upload/pending state. Automated tests also cover private storage, pending duplicate cleanup, role gates, upload rejection and owner/account preservation. Production MySQL concurrency, email delivery and deployment remain separate prelaunch gates.

### Admin workspace

Admin dashboard now uses actual user/course/enrollment totals and review-queue counts, with no template revenue or reports. Matching Arabic admin navigation links taxonomy, course/review moderation and instructor applications. Admin authentication screens share the visual system while retaining the admin guard and routes.

Taxonomy routes use a validated controller with parent-bound subcategory reads/writes, stable nonempty slugs for Arabic names, correct-table uniqueness, safe image validation and preservation of in-use course metadata. Failed upload/database writes clean new files; old images are removed only after a successful save. Existing unused legacy taxonomy controllers are not used by web routes.

Instructor review decisions are explicit form actions, require the same current application snapshot shown on screen, and cannot re-decide an already-approved account. Approval requires an available private document. Ordinary pending learner accounts without application documents no longer appear in the request queue. Existing decision emails remain configured behavior; local browser tests use log mail, not external sends. This does not establish production mail delivery or concurrent MySQL behavior.
