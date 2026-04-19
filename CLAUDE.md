# CLAUDE.md — CampusLearn LAN Portal

Project-scoped rules for the CampusLearn Student Information & Billing Portal.
These rules override any conflicting general defaults.

---

## Stack (immutable)

| Layer | Technology |
|---|---|
| Frontend | Vue 3, TypeScript, Vite, Vue Router, Pinia, Axios, Vitest, Vue Test Utils, IndexedDB |
| Backend | PHP 8.3, Laravel 13, Eloquent, Form Requests, Policies/Gates, Jobs/Queues, Pest/PHPUnit |
| Database | MySQL 8.4 (all monetary values in cents) |
| Containers | docker-compose.yml + per-service Dockerfiles |

**Never substitute** a different framework, ORM, test runner, or database engine.
**Never add** internet-dependent services, external payment processors, cloud storage, SaaS messaging, or hosted IdPs.

---

## Folder Contract

```
TASK-31/
  docs/                     ← design.md · api-spec.md · questions.md · traceability docs
  repo/
    README.md               ← keep synchronized; real behavior only
    docker-compose.yml
    run_tests.sh            ← do not rename
    frontend/
      Dockerfile
      src/
      public/
      unit_tests/           ← Vitest unit tests
      e2e/                  ← Playwright-style E2E tests
    backend/
      Dockerfile
      src/
      database/
      unit_tests/           ← Pest/PHPUnit unit tests
      api_tests/            ← no-mock API/integration tests (dominant)
  sessions/                 ← DO NOT TOUCH, EVER
  execution_plan.md         ← DO NOT EDIT
  metadata.json
```

Prohibited locations: root-level `unit_tests/`, root-level `API_tests/`, trajectory files, bugfix logs, session exports, prompt-numbered filenames.

---

## Offline / LAN-only (non-negotiable)

- Zero calls to external payment processors, identity providers, or cloud services.
- All payments handled at local staffed office or kiosk only.
- Notifications delivered via local database queue only.
- Backups written to local encrypted files only.
- Service worker permitted only where it materially improves poor-network recovery.

---

## Authorization — enforce at every layer

1. Route middleware (authentication gate)
2. Controller/Policy check (role-level)
3. Object-level check (owns this record?)
4. Scope-level check (term? course? section? grade item?)

Multi-role accounts use scoped grants. Least-privilege always. Audit-log all privileged mutations.

---

## Billing / Ledger Rules

- All amounts stored in cents; displayed as two-decimal.
- Tax rules configurable per fee category.
- Idempotency keys on every completion endpoint — no double-posting.
- Recurring billing: scheduler fires 1st of month at 02:00 AM.
- Penalty: 5% after 10 days past due.
- Refunds: partial amounts allowed; reason code required; generates reversal ledger entry + reconciliation flag.
- End-of-day closeout uses reconciliation flags.

---

## Discussion / Moderation Rules

- Student edit window: 15 minutes from post creation — enforced server-side.
- Sensitive-word filter: runs at submit time, highlights blocked terms, blocks publish until rewrite.
- Moderation state machine: `visible → hidden → restored` or `locked` (not a boolean toggle).
- @mentions always trigger notifications.

---

## Order / Payment Workflow

- Status lifecycle: `pending_payment → paid → canceled → refunded → redeemed`
- Incomplete payment flows auto-close after 30 minutes (scheduler job).
- Every completed payment produces a printable receipt.

---

## Notifications

- Notification Center: unread count badge, bulk mark-as-read, per-category subscription.
- Categories: `announcements`, `mentions`, `billing`, `system`.
- Event triggers: enrollment outcome, grade publication, appointment change, billing outcome.

---

## Auth / Security

- Password minimum: 10 characters, bcrypt-salted hash.
- Lockout: 5 failed attempts → 15-minute account lock.
- Session tokens: short-lived, stored locally only, rotated on refresh.
- Sensitive data masked in UI by default; protected at rest.
- Never hardcode secrets, credentials, or demo-bypass flags.

---

## Observability

- Structured JSON logs with correlation IDs on every request.
- Request metrics; health endpoints exposing DB and queue status.
- Alert threshold: error rate >2% sustained for 5 minutes.
- Circuit breaker: on trip, serve read-only cached views with clear UI indicator.
- Diagnostic export: AES-encrypted local file.

---

## High Availability / DR

- Client: IndexedDB cache + exponential backoff retry on network failure.
- Server: local message queue for notification fan-out.
- Nightly encrypted backups, 30-day retention.
- `docs/` must contain restore runbook and quarterly DR drill procedure.

---

## Testing Rules

| Suite | Location | Tooling | Requirement |
|---|---|---|---|
| Frontend unit | `repo/frontend/unit_tests/` | Vitest + Vue Test Utils | Required |
| Frontend E2E | `repo/frontend/e2e/` | Playwright-style | Required |
| Backend unit | `repo/backend/unit_tests/` | Pest/PHPUnit | Required |
| Backend API/integration | `repo/backend/api_tests/` | Pest/PHPUnit HTTP tests | Required — no-mock dominant |

- Every endpoint must have API test coverage.
- No-mock HTTP tests must outnumber mock-heavy tests.
- `run_tests.sh` must orchestrate all suites docker-first.
- **Do not run Docker or tests during implementation prompts.**

---

## Documentation Rules

- `docs/questions.md` is the sole ambiguity log — log every unresolved assumption there.
- `repo/README.md` describes only real behavior, real ports, real commands.
- `docs/design.md` and `docs/api-spec.md` stay synchronized with implementation.
- `docs/requirement-traceability.md` and `docs/test-traceability.md` maintained throughout.
- No prompt-number references in filenames, section headings, or doc content.

---

## What NOT to do

- Do not run `docker compose up` or any test suite during implementation.
- Do not create stubs, hardcoded success paths, or demo-only placeholders as final deliverables.
- Do not use god files, god controllers, or route closures containing business logic.
- Do not touch anything under `sessions/`.
- Do not edit `execution_plan.md`.
- Do **not** create parallel subagents in plan mode or agent mode.(CRITICAL)
- Do not add internet-dependent dependencies to package.json or composer.json.

## When in plan mode AND when implementing a plan(this also applies to non-planned implementations)
- Always explicitly include the first two lines of each prompt given, objective and exact scope,contextual self check, Explicit constraints and completion criteria, make all of these copy pasted exactly into the plan document.
- DONT RUN PARALLEL SUBAGENTS, ALL EXPLORATION (ONLY RELEVANT CODE) AND IMPLEMENTATION MUST BE DONE SEQUENTIALLY BY THE MAIN AGENT