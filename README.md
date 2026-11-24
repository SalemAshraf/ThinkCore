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

## Remaining product and prelaunch work

Current source supports free text courses, sections, moderated publication/reviews, catalog/search, enrollment/progress/library, instructor studio/account/application and administrator taxonomy/review workspaces. The root homepage now uses real approved catalog data. Earlier milestone counts below are historical, not the latest full suite result.

Remaining work is not an implicit authorization to choose or buy a service:

1. Paid orders, signed idempotent payment webhooks, refunds and instructor settlement are not implemented. Provider, currency and business rules require decisions before implementation or payment activity.
2. Private video/attachments need storage/delivery design and entitlement enforcement. External demo links are public previews only. Do not promise screen-recording prevention.
3. Course edits currently withdraw publication pending review, including learner lesson access. Keep this behavior until the owner decides whether enrollment access should persist through edits.
4. Instructor decision notifications now persist atomically in an outbox, but delivery is deliberately not enabled. Review an approved mail transport, worker/command operation, monitoring and recipient policy before enabling sends. No live mail is exercised by local tests.
5. Production qualification: real concurrent MySQL tests, backup/restore, deployment/recovery, rate limits, API/file permissions, accessibility, privacy/terms and reviewed migration of any legacy public applicant documents.
6. Safe draft-course deletion is not implemented. No course deletion/production-data cleanup should be inferred.

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

### Write-integrity hardening

Active web course-metadata writers now lock and recheck selected taxonomy records inside their transaction. Admin deletion locks the same taxonomy row and performs current locking reads for course/child references before deletion; nested child writes lock their parent first. This closes the previous check-delete gap for those coordinated active paths, with the local multi-process MySQL qualification described below. There are still no database foreign keys on these legacy taxonomy columns, so scripts or future writers that bypass this protocol are not protected. Such writers must join the protocol or a separately audited constraint migration is required.

Course thumbnail creation/replacement now removes new uploads when database writes fail and defers deletion of the old image until transaction commit. Cleanup failures are reported without deleting the newly committed image; orphan-file reconciliation remains an operational concern. Upload storage failures are no longer silently treated as success. Regression tests inject failed course inserts/updates and verify original bytes and absence of leaked new files. Source tests do not prove concurrent MySQL execution or production migration readiness. Learner publication/access behavior and external mail behavior are unchanged.

### Local MySQL concurrency qualification

`tests/Concurrency/run-local-mysql.sh` downloads an unprivileged MySQL 8.0.39 runtime and libaio to a dedicated local directory, runs only a fictional local database on port 3317/socket, applies migrations there, calls real controllers in independent PHP workers, and stops its server on exit. Requires curl, tar, dpkg-deb, PHP with pdo_mysql, and a dedicated local checkout with Composer dependencies. Never point this harness at a shared/production database. It ignores external DB credentials and does not send email. Example: `APP_DIR=$PWD PHP_BIN=php bash tests/Concurrency/run-local-mysql.sh`.

Qualification on MySQL 8.0.39/InnoDB REPEATABLE READ exposed a real deadlock in the first published locking implementation. Course metadata now retries its side-effect-free transaction up to five times, resolving the owned course on every attempt. Thumbnail writes are deliberately outside that retry region. The strengthened harness waits for each worker's actual lock attempt before releasing the first worker. Three rounds of both lock orders for category/level/language assignment versus deletion, plus child creation versus parent deletion, completed 24 controlled pairs per run without dangling references or unexpected worker errors after the fix. Exhausted-deadlock injection verified five attempts and unchanged course/image bytes. The full SQLite suite remains 84 tests / 413 assertions.

This qualifies those paths on the tested local engine/isolation, not all possible schedules, production configuration, deployment, backup/restore, the entire feature suite on MySQL, or writers that bypass the protocol. Deadlock exhaustion can still surface an error after five attempts, but must not commit partial metadata or file changes.

### Instructor decision notification outbox

The active administrator decision route now commits an immutable recipient/name/decision snapshot in `instructor_decision_notifications` in the same transaction as approval/rejection. It never calls a transport from that HTTP action. A unique event key and stale application snapshot protect replay; outbox persistence failure rolls the role decision back. Additive migration only, no historical decisions are backfilled or resent. User IDs are audit references without a cascade FK so an eventual account deletion cannot silently erase pending notification state; retention/deletion policy needs review before production.

`php artisan thinkcore:deliver-instructor-decisions` is a dry-run count only. Actual transport requires explicit `--send`; nothing schedules this command or starts a worker. `--limit` is bounded to 1-100, default25. `--retry=ID --send` resets one terminal failed event before delivery processing. The command uses the configured Laravel transport; `MAIL_QUEUE` no longer controls this decision path. Do not enable sending against real recipients until delivery scope, sender/transport, operational monitoring and deployment are approved. Tests use Mail::fake, not real SMTP.

Each event has pending/sending/sent/failed state, attempt count, next-attempt/sent/failed timestamps and claim token/time. Failures store the exception class only, not transport text that could contain credentials. Retry backs off 2/4/8/16 minutes, stopping after five attempts. Terminal failure blocks later events for the same applicant until explicitly retried and delivered, preventing a later approval from overtaking an undelivered rejection. A 15-minute abandoned claim can be reclaimed; transport timeouts must be shorter than that lease. Sequential repeated calls skip a sent event, and two controlled MySQL workers produced one fake send/one skipped claim with one attempt.

Delivery is at-least-once, not exactly-once: transport acceptance followed by a process/database failure before recording sent can cause a duplicate on retry. A worker alive beyond its claim lease can overlap a replacement worker. Sent means accepted by the configured transport, not delivered/read. Manual retry, stale claim recovery and terminal-event ordering need operational monitoring; no UI, provider, scheduler or live email was enabled in this slice. Existing mail wording/design remains unchanged. Learner access policy remains unchanged.

Verification: 89 tests / 470 assertions, including immutable context after user edits, no send during decision, stale replay, atomic rollback, fake transport failure/backoff/exhaustion/manual retry, active/abandoned claim handling, rejection→approval order and command dry-run. Local MySQL8.0.39 REPEATABLE READ also ran the two-process outbox claim check with fake mail, alongside the existing24 taxonomy race pairs. Finite local checks are not production qualification.

### Full local MySQL feature-suite follow-up

The full 89-test / 470-assertion suite now also passes on local MySQL8.0.39, in addition to SQLite. The first MySQL pass exposed a test-only defect: injected outbox persistence failure matched SQLite double-quoted SQL only, so it did not inject on MySQL. The fault selector now matches the insert operation/table independent of SQL identifier quoting; the rollback assertion passes on both engines. No application behavior changed in this follow-up.

Set `RUN_FEATURE_SUITE=1` with the local MySQL harness to include the full suite in its separate `thinkcore_fictional_features` database, after concurrency checks. This database is exclusively for fictional tests and is reset by Laravel's test lifecycle. Never use that database name for real data. Tests fake mail and run only on the local socket. These checks do not prove production deployment, backup/restore, real email delivery, or every race schedule.

### Registration and profile file lifecycle

Instructor registration validates/stores its private application document, then creates the account transactionally. Failed account writes clean the new document. The existing pending-application queued email now captures immutable pending status/name rather than serializing the mutable User model. Notification queue/verification exceptions after account creation no longer turn that committed account into a failed registration response: login completes and a clear warning explains that the account exists and verification can be requested again. Recipient, wording, pending role and verification policy are unchanged. Failures log user ID/error class only.

This is not a registration notification outbox: pending-message queue failure is logged, not durably retried, and verification recovery uses the existing resend flow. Admin decision outbox coverage remains separate. No actual email/provider/worker was enabled. Profile avatar writes lock the current user, preserve the old file until save commits and remove the new file on failed save; email edits still clear verification. Cleanup failures can leave orphans and need operational reconciliation.

Verification: 95 tests / 506 assertions pass on both SQLite and isolated local MySQL8.0.39. New regressions inject account/profile write failures and pending/verification notification failures, verify private-file cleanup, stable queued snapshot, usable replacement avatar and preserved original bytes. A real local mobile browser completed fictional instructor registration against an intentionally invalid local queue connection, remained logged in and displayed the warning without overflow/JS errors. Actual warning pixels were inspected at390px. No customer account, external mail or production system was touched.
