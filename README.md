# Tuntas — Personal Todos, Team Boards, Done Together

> **Tuntas** *(n.) — Indonesian for "thoroughly done" (pronounced "TOON-tahs").* A Laravel v1 portfolio project: not the Todo idea being judged, but **senior-grade execution** — isolation, authorization, validation, notifications, search, sort, pagination, and tests (18/18 green).

## 2-minute demo

Log in as a reviewer — every feature reachable with zero manual setup:

| Account | Password |
|---------|----------|
| `demo@tuntas.test` (owner) | `password` |
| `budi@tuntas.test` (member) | `password` |

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Clickable flow: `/todos` (All/My/Team scopes + Active/Archived/Overdue filters + search + detail) → `/teams` (Tim Demo) → `/teams/{id}/members` (invites) → 🔔 bell (All/Tasks/Invites tabs) → `/notifications` → `/todos/{id}` (full edit).

| Try | Expect |
|-----|--------|
| Guest opens `/todos` | `302 → /login` (`auth` middleware) |
| Log in as budi, peek at demo's todos | No leak (`visibleTo` scope) |
| Guess `PATCH /todos/{id}` owned by demo | `403` (TodoPolicy) |
| Assign a todo to budi | budi's 🔔 bell +1 (TodoAssignedNotification) |
| `POST /todos` empty title / `urgent` priority | validation errors (FormRequest) |
| Invite the same email twice | `Already invited` (pending unique) |
| Accept an already-declined invite | `Invite already handled` (status guard) |

## v1 features

- **Auth** — Breeze (Blade); email verification OFF by design (database-only notifications, zero demo friction; ADR-9)
- **Todo details** — `priority` (low/medium/high) + `due_date` + `description`; nearest-due-first sort + priority tie-break; `Due/Overdue` badges
- **Detail page** — `GET /todos/{todo}` is the single place for full edits (slim list: title + badges + actions)
- **Teams** — create, per-team board, paginated 10 (Owned vs Member split, no duplicates)
- **Members** — dedicated `/teams/{team}/members` page (list + roles + invite + kick); owner-only invites (ADR-8)
- **Invites** — pending/accepted/declined + two-way status guards + duplicate-pending prevention
- **Hybrid notifications** — bell dropdown with All/Tasks/Invites tabs (Alpine) + `/notifications` page + mark-read / mark-all-read
- **Search** — title + description inside a group (no scope leak), `?q` retained via `appends()`
- **UI/UX** — 3 button variants (hover/active/focus-visible), visible labels on every input, `aria-label`s, 360px-responsive (`min-w-0`), friendly empty states

## Stack

Laravel 11 · PHP 8.5 · SQLite · Blade + Tailwind · Alpine.js (dropdown tabs) · Vite · PHPUnit (18 tests, 51 assertions)

## Tests

```bash
php artisan test --filter="TodoTest|TeamTest"   # 18/18: scoping, 403 policy, validation, search, sort/filter, notifications, detail page
php artisan test --filter="PasswordReset|PasswordUpdate|PasswordConfirmation"  # 9/9: reset via log mailer
```

## ADR (short)

1. **Scope in query, not in Blade** — `visibleTo()` + `where user_id`: a leak becomes impossible to miss in one view.
2. **Policy over abort** — single-source `canManage` (`TodoPolicy`); scattered `abort_unless` = two truths.
3. **FormRequests, not inline validate** — reusable + testable + consistent messages.
4. **`when` + `appends`** — conditional filters/search + query retained on page 2.
5. **Nulls-last sort + CASE** — SQLite sorts NULL first on ASC; string priorities mapped to numbers.
6. **Database-only notifications** — no SMTP needed for v1; `log` mailer suffices for password reset.
7. **UI `Tasks`, code `Todo`** — model rename expensive for zero gain; labels swept Blade-only.
8. **Owner-only invites** — corporate model; members may still create todos (only `view` needed).
9. **Email verification OFF** — demo friction > benefit while notifications are non-email; factory-verified seeds migrate smoothly later.
10. **403-before-status** — authorize before business checks, prevents oracle info leaks.

## v2 roadmap

Invite expiry · member-can-invite · promote/demote · custom 403/404 pages · user-selected sort · dark mode · localization · member profiles.
