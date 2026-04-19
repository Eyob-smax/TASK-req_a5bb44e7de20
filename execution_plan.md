# General Instructions

Treat this project as a **full-stack offline LAN student information, communications, and billing platform**. Build a Vue.js browser client in `repo/frontend/`, a Laravel backend in `repo/backend/`, and MySQL as the system of record. Keep the product fully functional inside a district-controlled local network with **no dependency on internet services**, third-party identity providers, hosted payment processors, SaaS messaging vendors, local modules or cloud storage. Keep the implementation centered on the actual business problem: student/course information management, role-aware dashboards, course announcements, threaded discussions, moderation, sensitive-word filtering, notifications, scoped permissions, roster imports, fee-based ordering, local payment workflows, billing generation, ledger/reconciliation, observability, offline resilience, and local disaster-recovery readiness.

Use stable, production-ready defaults where the prompt is silent, and keep those defaults explicit everywhere:
- **Frontend:** Vue 3, TypeScript, Vite, Vue Router, Pinia, Axios, Vue Test Utils, Vitest, Playwright-style E2E coverage authored under `repo/frontend/unit_tests/`(e2e can have their own folders), IndexedDB-backed cache/queue helpers, and a service worker only where it materially improves poor-network recovery.
- **Backend:** PHP 8.3, Laravel 13, Eloquent ORM, Form Requests, Policies/Gates, Jobs/Queues, Scheduler, Notifications using local/database delivery only, structured logging, Pest/PHPUnit, and Laravel HTTP/integration tests.
- **Database:** MySQL 8.4 with all monetary values stored in cents.
- **Containerization:** `repo/docker-compose.yml`, `repo/frontend/Dockerfile`, and `repo/backend/Dockerfile` with no undeclared host dependencies and no hidden sidecars.
- **Testing:** frontend unit and E2E tests inside `repo/frontend/unit_tests/` and `repo/frontend/e2e/` respectively; backend unit tests inside `repo/backend/unit_tests/`; backend API/integration tests inside `repo/backend/api_tests/`; `repo/run_tests.sh` must orchestrate all suites in a docker-first way without being run during these prompts.

Honor this repository contract exactly:
```text
TASK-31/
├── docs/
│   ├── design.md
│   ├── api-spec.md
│   ├── questions.md
│   ├── requirement-traceability.md
│   ├── test-traceability.md
│   └── ...
├── repo/
│   ├── README.md
│   ├── docker-compose.yml
│   ├── run_tests.sh
│   ├── frontend/
│   │   ├── Dockerfile
│   │   ├── src/
│   │   ├── public/
│   │   ├── unit_tests/
│   │   └── ...
│   └── backend/
│       ├── Dockerfile
│       ├── src/
│       ├── database/
│       ├── unit_tests/
│       ├── api_tests/
│       └── ...
├── sessions/                  # exists but must not be touched
├── prompt.md                  # already exists / original prompt source file(leave it if it doesnt exist its not that important)
├── execution_plan.md
└── metadata.json
```
Keep `docs/questions.md` as the ambiguity log for this project. Do not create root-level `unit_tests/` or root-level `API_tests/`. Do not rename `run_tests.sh`. Do not create or modify anything inside `sessions/`. Do not create trajectory files, bugfix logs, session exports, or any session-style artifact.

Create project-specific Claude memory before Prompt 1. Keep that memory brief and limited to critical anti-drift reminders for this repository only. Create project-specific `CLAUDE.md` rules for this repository only. Place both in the repository’s Claude project context. Keep both project-scoped, not user-level. Make them lock in: the Laravel + Vue + MySQL stack; offline-LAN-only behavior; local-only payment handling; scoped authorization rules; discussion/moderation invariants; billing/ledger/refund invariants; observability and backup expectations; `docs/questions.md` as the ambiguity log; the strict folder contract; the prohibition on running Docker or tests yet; requirement-to-module traceability; requirement-to-test traceability; and README/document authenticity. After creating the project-specific memory and `CLAUDE.md`, output their contents for user review before continuing. Do not place the literal contents of that memory or `CLAUDE.md` inside this execution plan(this execution plan is uneditable). Put explicitly parallel subagents are prohibited in plan mode nor agent mode. Exploration(only relevant codes) and implementation should be done by the main agent seqentially.

Keep the architecture modular and layered:
- Separate frontend views, components, stores, composables, adapters, offline-cache helpers, and shared UI primitives.
- Separate backend routes/controllers, request validators, policies, services, domain rules, repositories/models, billing engines, queue jobs, schedulers, logging/observability utilities, and backup/export modules.
- Avoid god files, god components, god controllers, and route files stuffed with business logic.
- Keep real business logic in code, not in comments, fake-success stubs, or demo-only placeholders.
- Centralize configuration. Use typed or clearly structured config files. Avoid absolute paths, host-only assumptions, and undocumented environment variables.
- Remove junk files, debug noise, commented-out dead code, and contradictory docs before completion.

Maintain full functional fidelity. By the end of Prompt 10, truthfully cover the explicit original-prompt requirements, including at minimum:
- role-aware home dashboards for students, teachers, registrars, and administrators;
- course announcements plus threaded discussion boards with comments, Q&A, `@mentions`, post/edit/report feedback, a 15-minute student edit window, and moderation hide/restore/lock controls;
- submit-time sensitive-word filtering that highlights blocked terms and requires a rewrite before publishing;
- a unified Notification Center with unread counts, bulk mark-as-read, category subscriptions, and alerts for enrollment outcomes, grade publication, appointment changes, and billing outcomes;
- multi-role accounts with fine-grained permission scopes down to term, course, class section, and specific grade items;
- registrar-scoped roster import for selected terms;
- fee-based service ordering for items such as lab fees and facility rentals, including order timelines, local staffed-office or kiosk payment workflows, printable receipts, and 30-minute auto-close behavior for incomplete payment flows;
- short-lived session tokens stored locally, password rules, salted hashing, and 15-minute account lockouts after 5 failed attempts;
- bills, ledger entries, refunds, receipt/invoice records, tax rules by fee category, recurring billing on the 1st at 2:00 AM, penalty charges at 5% after 10 days past due, idempotent completion endpoints, partial refunds with reason codes, reversal entries, and reconciliation flags;
- structured logs with correlation IDs, request metrics, health dashboards for database and queue status, alert-threshold tracking, circuit breaking with fallback to read-only cached views, and encrypted diagnostic exports;
- poor-network caching, automatic retries with exponential backoff, local message queues for notification fan-out, nightly encrypted backups retained for 30 days, a documented restore runbook, and quarterly disaster-recovery drill support.
Do not replace these with easier substitutes.

Design security and validation early and enforce them throughout:
- Use least-privilege role boundaries for Student, Teacher, Registrar, and Administrator accounts, while allowing multi-role identities with scoped grants.
- Enforce route-level, function-level, object-level, and scope-level authorization where relevant.
- Validate all body, query, path, file, and scheduler inputs.
- Enforce minimum password length, lockout rules, local token expiry/rotation, and secure hashing.
- Keep sensitive records masked by default in the UI and protected at rest in the database.
- Protect billing completion, refund, moderation, roster import, grade publication, export, and admin configuration flows with explicit authorization and audit logging.
- Prevent duplicate financial posting and duplicate payment completion with idempotency keys and replay-safe endpoints.
- Never hardcode secrets, credentials, demo-only bypasses, or plaintext passwords.

Keep backend and UI failure handling professional:
- Return structured, user-safe backend errors.
- Show meaningful loading, empty, success, warning, conflict, disabled, retry, and failure states in the UI.
- Highlight moderation blocks, validation failures, permission failures, duplicate submissions, stale cached data, retry queues, and read-only fallback states clearly.
- Use logs that help operations staff troubleshoot real incidents. Do not leak secrets, tokens, raw payment payloads, or raw sensitive student data.

Treat documentation as part of the product:
- Create `repo/README.md` early and keep it synchronized continuously.
- Make README describe only real behavior, real services, real ports, real commands, real verification steps, and real offline/local-network constraints.
- Keep `docs/design.md`, `docs/api-spec.md`, `docs/questions.md`, and any requirement traceability documents synchronized with the implementation.
- Maintain requirement-to-module and requirement-to-test traceability throughout all 10 prompts.
- Avoid prompt-number references in filenames, README sections, or implementation docs.

Keep Docker and tests in readiness mode only during this plan:
- Do not run Docker.
- Do not run the app.
- Do not run tests.
- Do not claim runtime success from documentation alone.
Write everything so a later reviewer can run `docker compose up` and `repo/run_tests.sh` without hidden manual steps.

Shape the repository for later static acceptance review from the start:
- Make repo structure, docs, configs, routes, policies, schemas, and tests easy to inspect.
- Keep endpoint inventory, permission boundaries, traceability, and authored test intent explicit.
- Make it easy for a reviewer to map prompt requirement → module → endpoint → test.
- Keep final deliverables looking like a real product, not a teaching demo or stitched sample.

Stay laser-focused on the current prompt by default. If the user intentionally pairs prompts, execute both prompts in sequence while preserving each prompt’s individual detail, traceability, documentation duties, and completion criteria. Treat pairing as a sequencing convenience only. Do not blur scopes together, simplify them away, or weaken either prompt.

# Self-Test Acceptance Criteria

Design the repository so it is prepared to pass a full-stack static delivery acceptance and architecture audit.

1. **Documentation and static verifiability**
   - Provide clear startup, configuration, verification, and test instructions.
   - Keep documented commands, ports, service names, folder paths, and entry points statically consistent.
   - Make the repo inspectable without forcing a reviewer to rewrite core code.

2. **Prompt alignment and no unauthorized simplification**
   - Keep the implementation centered on the district-operated student information and billing scenario.
   - Do not replace core requirements with easier stand-ins.
   - Preserve the real business semantics of moderation, scoped permissions, local payment handling, billing generation, refunds, reconciliation, observability, and offline resilience.

3. **Delivery completeness**
   - Deliver a real engineered project, not snippets, mock-only shells, or disconnected page samples.
   - Implement core logic truthfully instead of relying on hardcoded success paths or demo-only state.
   - Keep README and design/API docs present and synchronized.

4. **Engineering structure and maintainability**
   - Keep modules reasonably decomposed and responsibilities clearly separated.
   - Avoid giant all-in-one files, chaotic coupling, and hidden business logic inside templates or route closures.
   - Keep the codebase extensible for future terms, courses, categories, and billing rules.

5. **Professional engineering details**
   - Use reliable validation, structured error handling, meaningful logging, and user-safe APIs/UI behavior.
   - Make billing, moderation, roster import, refund, and export flows auditable and operationally credible.
   - Keep logs and responses free of sensitive leakage.

6. **Prompt understanding and implicit-constraint fit**
   - Respect the offline/local-network constraint across announcements, payments, notifications, diagnostics, and backups.
   - Handle implicit rules such as scoped role precedence, edit-window enforcement, moderation state transitions, fee tax rounding, duplicate-payment prevention, and read-only fallback behavior explicitly.

7. **Testing readiness and test depth**
   - Author frontend unit tests, frontend E2E tests, backend unit tests, and backend API/integration tests where relevant. Give more focus for api tests.
   - Keep tests in the required folders and orchestrate them through `repo/run_tests.sh` using a docker-first approach.
   - Cover happy paths, validation failures, authorization failures, not-found cases, conflict/idempotency behavior, security-sensitive flows, user-visible failure handling, and important domain invariants.
   - Distinguish true no-mock HTTP/API coverage from mock-heavy tests. Do not treat mock-heavy API tests as equivalent to full endpoint coverage.
   - Do not rely on mock API tests as acceptance-grade substitutes for backend API/integration coverage on critical routes. No-mock tests should be dominant over mock tests. every single endpoint must have test coverage.
   - Keep endpoint inventory and requirement-to-test traceability explicit.
   - Keep frontend/backend coverage balanced. Do not leave the UI effectively untested in a full-stack project.

8. **Security focus**
   - Make authentication, authorization, scope enforcement, admin protection, moderation controls, billing protection, refund controls, audit immutability, token handling, and sensitive-data protection explicit and testable.
   - Verify object-level and scope-level restrictions, not just route middleware.

9. **Frontend / UX quality for this full-stack project**
   - Present a coherent role-aware application shape with differentiated functional areas.
   - Show usable interaction feedback for posting, moderation, billing, payment completion, refund flows, imports, retries, and offline/read-only conditions.
   - Keep the UI credible for real school operations rather than dashboard theater.

10. **Final static audit awareness**
    - Make Prompt 10 perform a strict static sweep of repo structure, docs consistency, endpoint inventory, test placement, Docker/service/port alignment, security boundaries, unresolved assumptions in `docs/questions.md`, and documentation honesty.
    - Make the final prompt instruct the implementation AI to tell the user to invoke the separate execution-plan reviewer workflow after the static readiness pass, without attempting that review itself.

# Original Prompt

Implement a CampusLearn Student Information & Billing Portal for a district-operated learning program where students, teachers, registrars, and administrators manage courses, communications, and fee-based services entirely on a local network. The Vue.js web interface provides a role-aware home dashboard with course announcements and threaded discussion boards that support comments, Q&A, and @mentions; users receive immediate UI feedback for posting, editing within 15 minutes, and reporting content, while a moderation view lets staff hide, restore, or lock threads. Sensitive-word filtering runs at submit time and highlights blocked terms with a required rewrite before publishing. A unified in-app Notification Center surfaces system notices and event-triggered alerts such as enrollment approval/denial, grade item publication, appointment changes, and billing outcomes, with unread counts, bulk mark-as-read, and subscription preferences per category (announcements, mentions, billing, system). The product supports multi-role accounts with fine-grained permissions down to term, course, class section, and specific grade items, so a teacher can publish only within assigned sections while a registrar can import roster updates for a selected term. For paid items such as lab fees or facility rentals, users can view an order timeline (pending payment, paid, canceled, refunded, redeemed) and initiate payments at a staffed office or kiosk workflow that produces a printable receipt; any payment-like steps are handled locally without external processors, and orders auto-close after 30 minutes if not completed.

The backend uses Laravel to expose REST-style APIs consumed by the Vue.js client, enforcing authorization on every request via server-side policy checks and issuing short-lived session tokens stored locally; passwords must be at least 10 characters with salted hashing, and accounts lock for 15 minutes after 5 failed attempts. MySQL persists users, roles, permission scopes, courses, posts, moderation actions, notifications, orders, bills, ledger entries, refunds, and receipt/invoice records; monetary values are stored in cents with two-decimal display and include configurable local sales tax rules per fee category. Billing supports automatic bill generation for initial, recurring (monthly on the 1st at 2:00 AM), supplemental, and penalty charges (5% after 10 days past due), with idempotent callback-style completion endpoints to prevent double-posting when staff retries a transaction; refunds support partial amounts and require a reason code, generating reversal ledger entries and reconciliation flags for end-of-day closeout. Observability includes structured logs with searchable correlation IDs, request metrics, health dashboards for database/queue status, alert thresholds (e.g., error rate >2% for 5 minutes), circuit breaking with fallback to read-only cached views, and diagnostic exports to an encrypted local file. High availability is achieved through poor-network caching on the client, automatic retries with exponential backoff, local message queues for notification fan-out, nightly encrypted backups retained for 30 days, and a documented restore runbook with quarterly disaster recovery drills on offline hardware.

# 10 Sequential Prompts

## Prompt 1 — Architecture framing, repo contract, and planning artifacts

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.

**Objective**  
Lock the project type, stack, repo contract, baseline docs, metadata, and endpoint/testing traceability skeleton so implementation starts from a truthful Laravel + Vue + MySQL foundation.

**Exact Scope**  
Work only on project framing artifacts and truthful scaffolding in `docs/`, `repo/`, `repo/frontend/`, and `repo/backend/`. Create the core folder structure, baseline manifests, initial docs, and the first requirement inventory. Do not implement core business workflows yet beyond structural anchors.

**Implementation Instructions**
1. Classify the repository as a **full-stack offline LAN web platform** and make that explicit in `metadata.json`, `repo/README.md`, and `docs/design.md`.
2. Create the initial folder structure:
   - `repo/frontend/` with `src/`, `public/`, `unit_tests/`, package/build config, and `Dockerfile`.
   - `repo/backend/` with `src/`, `database/`, `unit_tests/`, `api_tests/`, build config, and `Dockerfile`.
   - `docs/design.md`, `docs/api-spec.md`, and `docs/questions.md`.
3. Record the actual stack explicitly:
   - Vue 3 + TypeScript + Vite + Vue Router + Pinia on the frontend.
   - PHP 8.3 + Laravel 13 on the backend.
   - MySQL for persistence.
   - Vitest + Vue Test Utils + Playwright-style authored E2E coverage inside `repo/frontend/unit_tests/`.
   - Pest/PHPUnit for backend unit and API/integration coverage.
4. Create a truthful `docs/design.md` covering:
   - offline district-network assumptions;
   - topology of frontend, backend, database, queueing, and local diagnostics/backups;
   - role model and scope model;
   - discussion/moderation architecture;
   - billing, ledger, refund, and reconciliation module boundaries;
   - observability, health, read-only fallback, and backup/restore boundaries;
   - requirement-to-module traceability table.
5. Create a truthful `docs/api-spec.md` covering:
   - request/response envelope conventions;
   - auth/session strategy at a high level;
   - endpoint inventory skeleton grouped by auth, dashboards, discussions, moderation, notifications, roster imports, orders, billing, refunds, diagnostics, backups, and admin settings;
   - conflict/error conventions;
   - idempotency-key conventions;
   - pagination/filter/sort conventions.
6. Create `docs/questions.md` first using only blocker-level or implementation-shaping ambiguities and the exact entry fields:
   - `The Gap`
   - `The Interpretation`
   - `Proposed Implementation`
   Capture assumptions for multi-role scope precedence, appointment modeling, payment completion semantics, sensitive-word rule management, tax rounding, callback/idempotency semantics, backup encryption, and disaster-recovery documentation scope.
7. Create the first truthful `repo/README.md` with:
   - project overview;
   - actual stack;
   - repo structure;
   - offline/local constraints;
   - note that Docker/test execution is intentionally deferred at this stage;
   - only real commands/files that exist after this prompt.
8. Create `metadata.json` that reflects the real stack for this prompt, including:
   - `Project Type: full-stack`
   - `prompt: the unedited original prompt`
   - `frontend_language: TypeScript`
   - `backend_language: Python`
   - `frontend_framework: Vue.js`
   - `backend_framework: FastAPI`
   - `database: PostgreSQL`
   Do not add a `current_prompt` field.
9. Add baseline package/build configuration for the frontend and backend, plus skeletal entrypoints that are real and importable.
10. Create truthful scaffolding for `repo/docker-compose.yml`, `repo/frontend/Dockerfile`, and `repo/backend/Dockerfile`. Keep them aligned with files that actually exist. Do not invent extra services.
11. Create a first requirement inventory and endpoint inventory document under `docs/` so later prompts can maintain exact requirement → endpoint → test mapping.
12. Create project-specific Claude memory and `CLAUDE.md` rules in the repository’s Claude project context before deeper implementation work, then output them for user review.

**Contextual self-checks for this prompt**
- Keep documentation statically verifiable and internally consistent immediately.
- Keep the repo structure aligned to the strict full-stack contract from day one.
- Make README describe only real files, commands, services, and constraints.
- Do not drift into a backend-only scaffold or a frontend-only mock shell.
- Make the endpoint inventory and requirement inventory visible now so later coverage is traceable.

**Test-Authoring Instructions**
- Create the test directory structure now: `repo/frontend/unit_tests/`, `repo/backend/unit_tests/`, and `repo/backend/api_tests/`.
- Add only minimal scaffold tests that verify something real, such as frontend config/bootstrap importability and backend application bootstrap/importability.
- Add a placeholder frontend E2E harness inside `repo/frontend/unit_tests/e2e/` that is real but minimal.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Synchronize `docs/design.md`, `docs/api-spec.md`, `docs/questions.md`, `repo/README.md`, `metadata.json`, and the requirement/endpoint inventory.
- Keep the original prompt unedited wherever it is recorded.
- Make the docs name the same stack, same folder structure, same offline constraints, and same test locations.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not try to build the whole project. Laser focus on framing and truthful scaffolding only.
- Do not create placeholder business logic, fake success APIs, or fake seeded workflows.
- Do not touch `sessions/`.
- Do not create root-level test folders.
- Do not put `questions.md` anywhere except `docs/`.

**Completion Criteria**
- The strict repo structure exists and reflects a real Laravel + Vue + MySQL project.
- Core planning docs, metadata, requirement inventory, and endpoint inventory exist and are synchronized.
- `docs/questions.md` captures blocker-level ambiguities with practical forward-moving assumptions.
- README, Docker scaffolding, and metadata are mutually consistent at this stage.
- Initial test folders exist in the correct locations.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 2 — Domain model, persistence schema, endpoint contracts, and billing rule backbone

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Define the domain truth, relational schema, permission model, endpoint contracts, and billing/ledger invariants so the project has a stable core before deep implementation starts.

**Exact Scope**  
Focus on backend domain modeling, MySQL schema/migration design, endpoint contract definition, and the related documentation. Do not build full frontend screens yet.

**Implementation Instructions**
1. Define entities, enums, and value objects for:
   - users, credentials, sessions, failed-login counters, role assignments, and permission scopes;
   - terms, courses, class sections, roster memberships, grade items, announcement threads, discussion posts, comments, mentions, reports, moderation actions, and sensitive-word rule sets;
   - notification categories, templates, subscriptions, delivery logs, unread states, and bulk-read operations;
   - fee categories, catalog items, orders, order timelines, staffed-office or kiosk payment attempts, receipts, invoices, bills, bill schedules, penalty jobs, tax rules, ledger entries, refund requests, refund reason codes, reversal entries, and reconciliation flags;
   - appointment or reservation entities needed to truthfully support appointment-change notifications;
   - observability metrics, circuit-break states, diagnostic export records, backup jobs, restore-drill records, and local queue job metadata.
2. Design MySQL schema and migrations in `repo/backend/database/` with explicit constraints and indexes for:
   - multi-role scope resolution;
   - unique roster and grade-item authorization boundaries;
   - announcement/discussion ordering and edit-window enforcement support;
   - mention/report/moderation lookup patterns;
   - order state transitions and 30-minute auto-close tracking;
   - bill recurrence schedules, penalty rules, idempotency, partial refunds, reversal entries, and reconciliation flags;
   - diagnostic export and backup retention lookup;
   - queue/health observability.
3. Define key business invariants in code comments/docs and scaffold domain services for:
   - 15-minute edit windows;
   - moderation hide/restore/lock semantics;
   - submit-time sensitive-word rejection with highlight payloads;
   - category-based notification preferences and unread counters;
   - teacher/registrar/admin scope boundaries;
   - order timeline states and local payment completion rules;
   - recurring billing at 2:00 AM on the 1st;
   - 5% penalty after 10 days past due;
   - idempotent transaction-completion callbacks;
   - partial refunds with mandatory reason codes and reversal entries;
   - 30-day encrypted backup retention.
4. Expand `docs/api-spec.md` with concrete endpoint groups and payload contracts for:
   - auth/session;
   - dashboards and role-aware summaries;
   - courses, sections, rosters, announcements, discussions, comments, mentions, and moderation;
   - notifications, preferences, unread counters, and bulk mark-as-read;
   - orders, order timelines, payment completion, receipts, bills, taxes, refunds, and reconciliation;
   - roster imports and grade-publication flows;
   - observability, health, diagnostics, backups, and admin settings.
5. Establish an endpoint inventory using unique `METHOD + fully resolved PATH` entries. Resolve prefixes and versioning now. Link each endpoint group back to a prompt requirement.
6. Update `docs/design.md` with sequence descriptions for:
   - announcement/discussion create → filter → moderate;
   - order create → pending payment → local payment completion/auto-close;
   - bill generation → penalty → refund → reconciliation;
   - notification fan-out and unread-state updates;
   - poor-network cache refresh and read-only fallback;
   - backup and diagnostic export generation.

**Contextual self-checks for this prompt**
- Make the data model directly aligned to the prompt, not generic school CRUD.
- Make schema choices support later security, auditability, moderation, billing, and retries without rework.
- Keep endpoint inventory explicit so later API coverage can be proven statically.
- Do not leave appointment-change alerts as a floating requirement without a real domain anchor.

**Test-Authoring Instructions**
- Add backend unit tests for scope resolution rules, edit-window calculations, sensitive-word match logic, tax/penalty calculations, bill schedule calculations, refund invariants, and idempotency key behavior.
- Add backend API contract tests for malformed payloads, expected error envelopes, and conflict/idempotency response shapes.
- Record which endpoints already have authored coverage and which remain uncovered in the endpoint inventory.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Keep `docs/design.md`, `docs/api-spec.md`, `docs/questions.md`, the endpoint inventory, the requirement-to-module mapping, and `repo/README.md` synchronized.
- Document any newly discovered blocker-level ambiguity in `docs/questions.md` only.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not build the frontend screens in this prompt.
- Do not use hardcoded JSON or fake seed records as a substitute for real domain/schema design.
- Do not skip billing, reconciliation, moderation, or observability entities because they feel secondary.

**Completion Criteria**
- Domain entities, schema design, endpoint inventory, and API contracts are prompt-faithful and implementation-ready.
- Business invariants are explicit in code/docs.
- Critical contract and invariant tests exist but remain unexecuted.
- Documentation remains synchronized and traceable.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 3 — Security foundation, scoped authorization, token/session handling, and shared backend infrastructure

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Implement the cross-cutting security and shared platform primitives that every later workflow depends on: authentication, lockouts, authorization, validation, logging, idempotency, and observability foundations.

**Exact Scope**  
Work on backend security infrastructure and shared foundations, plus minimal frontend auth/session plumbing required for later role-aware screens. Do not implement the full business workflows yet.

**Implementation Instructions**
1. Implement backend authentication and session/token management with:
   - password minimum length enforcement at 10 characters;
   - salted secure hashing;
   - short-lived locally stored session-token support;
   - 15-minute account lockout after 5 failed attempts;
   - role issuance and session-context scope loading.
2. Implement scoped authorization primitives for:
   - route-level protection;
   - policy/service-level enforcement;
   - object-level checks for posts, grade items, orders, bills, refunds, and exports;
   - scope-level checks for term, course, section, and grade-item access.
3. Implement reusable request validation, error-envelope shaping, correlation-ID injection, and structured logging.
4. Implement idempotency middleware or equivalent reusable protection for retry-prone completion endpoints.
5. Implement health/metrics primitives for request metrics, queue status, database status, error-rate aggregation, and circuit-break triggers.
6. Implement diagnostic-export and backup encryption helpers at a shared-infrastructure level.
7. Add minimal frontend auth/session shell wiring:
   - login route/page shell;
   - guarded-route strategy;
   - local session bootstrap and expiry handling;
   - role-aware navigation placeholders;
   - shared unauthorized/session-expired UX patterns.

**Contextual self-checks for this prompt**
- Make authentication, authorization, privilege boundaries, and traceability explicit and testable.
- Keep scope enforcement deeper than route middleware alone.
- Keep session/token logic aligned with the prompt’s short-lived token requirement and offline/local constraints.
- Keep logs useful without leaking secrets, tokens, or sensitive student/financial data.

**Test-Authoring Instructions**
- Add backend unit tests for password policy, lockout timing, token expiry handling, idempotency helpers, scope resolution, authorization checks, correlation-ID propagation, and alert-threshold calculations.
- Add backend API/integration tests for unauthenticated access, unauthorized scope access, lockout behavior, expired-token behavior, and secret-safe error responses.
- Add frontend unit tests for route guards, session-expiry UX, unauthorized rendering, and role-aware nav gating.
- Mark true no-mock HTTP coverage separately from mocked tests in the endpoint inventory.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `docs/design.md` with auth, scope, session, observability, and circuit-break foundations.
- Update `docs/api-spec.md` with auth/session/error/idempotency details.
- Update `repo/README.md` only with real security/config behavior that now exists.
- Keep `docs/questions.md` honest about unresolved session-storage or encryption-assumption gaps.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not hardcode secrets, admin bypasses, or fake roles.
- Do not leave authorization as documentation-only intent.
- Do not leak raw stack traces, tokens, or sensitive fields to frontend responses.

**Completion Criteria**
- Security-critical primitives are implemented and reusable.
- Frontend auth shell foundations exist without overreaching into future business screens.
- Tests cover major security and auth failure paths.
- Docs reflect the actual security and observability foundations.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 4 — Core backend business engines: discussions, notifications, scoped academic workflows, orders, and billing

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Make the backend’s main business engine real: announcements/discussions, moderation, notifications, roster imports, order/payment orchestration, billing generation, refunds, ledger posting, reconciliation, and resilience jobs.

**Exact Scope**  
Work on backend services, repositories, state transitions, queues, scheduler jobs, persistence, and API handlers for the core business workflows. Keep frontend changes limited to API compatibility only.

**Implementation Instructions**
1. Implement announcements and threaded discussions with:
   - thread/post/comment/Q&A creation and retrieval;
   - `@mention` parsing and notification hooks;
   - report-content workflows;
   - 15-minute edit-window enforcement where applicable;
   - moderation hide/restore/lock actions with audit trails.
2. Implement submit-time sensitive-word filtering with:
   - normalized blocking rules;
   - response payloads that identify blocked terms and locations for UI highlighting;
   - mandatory rewrite enforcement before publish.
3. Implement notification logic with:
   - category preferences;
   - unread counts;
   - bulk mark-as-read;
   - event-triggered notices for enrollment decisions, grade-item publication, appointment changes, discussion mentions, and billing outcomes;
   - local queue fan-out and delivery logging.
4. Implement scoped academic workflows with:
   - teacher publish rights limited to assigned sections/grade items;
   - registrar roster-import rights limited to selected terms;
   - grade publication and enrollment decision event hooks that drive notifications.
5. Implement paid-item ordering with:
   - order creation and timeline transitions for pending payment, paid, canceled, refunded, and redeemed;
   - 30-minute auto-close for incomplete payment flows;
   - staffed-office or kiosk completion endpoints using idempotency keys;
   - printable receipt and invoice record creation.
6. Implement billing engines with:
   - initial, recurring, supplemental, and penalty charges;
   - recurring billing on the 1st at 2:00 AM;
   - 5% penalty application after 10 days past due;
   - local sales tax rules per fee category;
   - ledger posting in cents;
   - reconciliation flags for end-of-day closeout.
7. Implement refund workflows with:
   - partial refunds;
   - mandatory reason codes;
   - reversal ledger entries;
   - reconciliation updates.
8. Implement queue/scheduler behavior for:
   - local notification fan-out;
   - recurring bills;
   - penalty runs;
   - auto-close cleanup;
   - backup metadata;
   - alert-threshold evaluation.
9. Expose real backend endpoints for all of these workflows with validation, authorization, idempotency, logging, and audit writes enforced.

**Contextual self-checks for this prompt**
- This is the highest business-risk stage. Implement real logic, not controller-shaped placeholders.
- Keep state transitions auditable and replay-safe.
- Make billing/refund/posting logic mathematically and procedurally coherent.
- Make moderation and sensitive-word behavior explicit instead of hand-waved.
- Keep queue-backed workflows inspectable and observable.

**Test-Authoring Instructions**
- Add backend unit tests for edit-window rules, mention extraction, sensitive-word highlighting, moderation state transitions, notification fan-out decisions, roster-import scope checks, order timeline transitions, auto-close timing, billing schedule generation, tax/penalty calculations, partial refunds, reversal entries, reconciliation flags, and alert-threshold triggers.
- Add backend API/integration tests for:
  - discussion create/edit/report/moderate flows;
  - sensitive-word rejection flows;
  - notification preference and bulk-read flows;
  - teacher/registrar scoped workflow permissions;
  - order create → pending payment → completion/auto-close flows;
  - duplicate completion retry/idempotency flows;
  - bill generation, penalty, refund, and reconciliation endpoints.
- Cover happy paths, validation failures, authorization failures, not-found paths, and conflict/idempotency behavior.
- Mark which critical routes now have true no-mock API coverage.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `docs/design.md` with stepwise business-flow descriptions for all implemented engines.
- Update `docs/api-spec.md` with real request/response examples.
- Update `repo/README.md` to reflect only real backend modules and capabilities that now exist.
- Update requirement-to-module and endpoint-to-test traceability artifacts.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not fake moderation, billing, or receipt generation behavior.
- Do not collapse all business rules into controllers or route files.
- Do not skip audit logging for moderation, payment completion, refund, or posting transitions.

**Completion Criteria**
- Core backend business engines are implemented with real persistence, validation, scheduling, and auditability.
- Critical backend workflow tests exist and are meaningful.
- Docs and traceability artifacts reflect the real implementation state.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 5 — Frontend shell, offline-capable client architecture, and secure service layer

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Build the Vue application shell and client architecture so later business screens sit on a maintainable, poor-network-aware, security-conscious frontend foundation.

**Exact Scope**  
Focus on frontend app structure, routing, shared layouts, stores, service adapters, local cache/queue primitives, read-only fallback handling, and secure API integration. Do not build every business screen in full yet.

**Implementation Instructions**
1. Implement the frontend application shell with:
   - Vue Router;
   - authenticated and unauthenticated layouts;
   - role-aware navigation groups;
   - guarded routes aligned to backend auth/scope rules;
   - global loading/error/session-expired handling;
   - a visible read-only fallback shell for circuit-break conditions.
2. Implement Pinia stores and service/composable boundaries for:
   - auth/session state;
   - dashboard summaries;
   - courses, sections, and discussion data;
   - notifications center state;
   - orders and billing state;
   - admin/observability state;
   - offline cache/retry state.
3. Implement a typed frontend API layer that handles:
   - session-token attachment;
   - correlation-ID propagation if owned by the client;
   - idempotency-key generation where the client owns retried actions;
   - normalized error handling;
   - retry/display logic that does not hide backend conflicts.
4. Implement poor-network client foundations with:
   - IndexedDB-backed caches for core read models;
   - pending-action queues for retryable operations;
   - exponential-backoff helpers for client-owned retries;
   - read-only fallback state when backend health/circuit-break conditions require it;
   - conflict-resolution models instead of silent overwrite behavior.
5. Implement reusable UI primitives needed by later screens:
   - dashboard cards, lists, tables, forms, tabs, timelines, filters, search inputs, status chips, receipt/statement panels, confirmation modals, toasts, banners, and empty/error states.
6. Implement masking helpers and permission-gated action helpers aligned to backend policies.
7. Implement the first real login page and post-login dashboard shell with real role-aware placeholders and no fake shortcut paths.

**Contextual self-checks for this prompt**
- Make the frontend look like a coherent application, not isolated component demos.
- Treat poor-network behavior and read-only fallback as core prompt requirements, not optional polish.
- Make error/loading/retry/conflict states visible in the client architecture now.
- Keep frontend test presence explicit and real.

**Test-Authoring Instructions**
- Add frontend unit tests for route guards, shell rendering, role-aware nav visibility, store behavior, error normalization, read-only fallback banners, offline queue persistence helpers, and masking helpers.
- Add frontend tests for cache rehydration, pending-action queue state, retry banners, and session-expiry behavior.
- Keep frontend unit tests clearly identifiable by direct file evidence. Do not rely on package manifests alone.
- Record `Frontend unit tests: PRESENT` in the test traceability doc once real test files exist.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `docs/design.md` with frontend architecture, store/service boundaries, offline cache/queue design, and read-only fallback behavior.
- Update `repo/README.md` to describe the actual frontend module layout and state architecture.
- Update the endpoint inventory and requirement-to-test mapping if frontend-owned retry/idempotency behavior changes the interaction contract.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not build all business screens in this prompt.
- Do not hardcode business outcomes into stores or views.
- Do not bypass backend policies in the frontend for convenience.

**Completion Criteria**
- A maintainable frontend shell and offline-capable architecture exist.
- Shared stores, services, and UI primitives are ready for real screens.
- Frontend tests cover the client foundation and poor-network/error-state primitives.
- Documentation stays synchronized with the actual client structure.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 6 — Primary user workflows and core full-stack screens

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Implement the main user-facing flows and screens so the application already supports the central day-to-day operations for students, teachers, registrars, and administrators.

**Exact Scope**  
Build the highest-priority frontend screens and connect them to the real backend logic from earlier prompts. Do not finish every secondary admin/operational surface yet.

**Implementation Instructions**
1. Implement role-aware home dashboards for students, teachers, registrars, and administrators with real summary data and actionable entry points.
2. Implement course announcements and discussion screens with:
   - thread lists and detail views;
   - comments and Q&A flows;
   - `@mention` UX;
   - post/edit/report feedback;
   - edit-window countdown or status display;
   - moderation controls where authorized;
   - sensitive-word rejection highlighting and required rewrite UX.
3. Implement the Notification Center with:
   - list/detail views;
   - unread counts;
   - bulk mark-as-read;
   - category preferences;
   - alert visibility for enrollment decisions, grade publication, appointment changes, mentions, and billing outcomes.
4. Implement scoped academic screens for:
   - teacher publish actions limited to assigned sections/grade items;
   - registrar roster import selection by term;
   - related permission-denied UX where actions are blocked.
5. Implement ordering and billing screens for:
   - fee/item browsing where applicable;
   - order create/view;
   - timeline states for pending payment, paid, canceled, refunded, and redeemed;
   - staffed-office or kiosk payment initiation/completion UX;
   - printable receipt view;
   - bill list/detail, charges, tax display, penalties, and refund-request/processing status where role-appropriate.
6. Implement administrator views for:
   - moderation queue and thread controls;
   - billing oversight summaries;
   - refund/reconciliation views;
   - diagnostics/health summary entry points.
7. Make all screens use real service-backed data and show meaningful loading, empty, error, success, conflict, submitting, retry, and disabled states.
8. Keep masked data masked by default and reveal only where policy explicitly allows it.

**Contextual self-checks for this prompt**
- Connected end-to-end task closure matters more than static screen quantity.
- Prompt-critical states such as blocked-word rewrite, permission denial, payment completion retry, and bill/refund status must be visible in the UI.
- Keep role scope and moderation controls faithful to backend rules.
- Do not let the app merely “look right” while core task closure remains incomplete.

**Test-Authoring Instructions**
- Add frontend unit tests for dashboard rendering, discussion flows, blocked-word handling, moderation visibility, notification preferences, bulk-read actions, role-scoped UI gating, order timeline rendering, payment completion states, receipt rendering, and billing/refund views.
- Add frontend E2E coverage for the main discussion, notification, and order/billing flows using authored E2E specs inside `repo/frontend/unit_tests/e2e/`.
- Add backend API/integration tests that support the same flows from the server side.
- Cover happy paths, validation failures, authorization failures, and conflict states.
- Keep frontend/backend coverage balanced and explicit.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `docs/design.md` with the screen map, workflow map, and role-to-screen matrix.
- Update `repo/README.md` to describe the screens and capabilities that now genuinely exist.
- Update `docs/api-spec.md`, the endpoint inventory, and requirement-to-test mapping if screen-driven contract refinements were made.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not leave screens disconnected from real backend logic.
- Do not skip empty/error/conflict/submitting states.
- Do not expose restricted data or unauthorized actions by default in the UI.

**Completion Criteria**
- Main user workflows are available end-to-end through real frontend/backend integration.
- Required UI states and role-aware flows are implemented.
- Tests cover primary screen logic and critical failure states.
- Docs reflect the real user-facing application shape.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 7 — Secondary required modules: observability, diagnostics, backups, roster operations, and recovery tooling

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Complete the remaining required operational modules that are essential for prompt completeness but secondary to the first-line academic and billing workflows.

**Exact Scope**  
Focus on admin/reporting/resilience surfaces, roster import operations, health dashboards, diagnostic exports, backup/restore tooling, and disaster-recovery documentation support.

**Implementation Instructions**
1. Implement observability and health surfaces for:
   - database status;
   - queue status;
   - request metrics;
   - alert-threshold status such as error rate above 2% for 5 minutes;
   - circuit-break/read-only fallback visibility.
2. Implement registrar/admin roster operations with:
   - term-scoped roster import surfaces;
   - import validation results;
   - import history and failure visibility;
   - audit logging for import actions.
3. Implement diagnostic export flows with:
   - encrypted local diagnostic file generation;
   - export history;
   - authorized download controls;
   - traceable metadata and audit records.
4. Implement backup/admin surfaces with:
   - nightly encrypted backup job status;
   - 30-day retention visibility;
   - restore-runbook access;
   - quarterly disaster-recovery drill logging/status screens.
5. Implement any remaining admin controls needed for notification categories, sensitive-word list management, fee-category tax settings, and refund-reason code administration if those are necessary for prompt completeness.
6. Implement read-only cached-view behavior for key pages when circuit-break conditions are active, with explicit banners and disabled write actions.

**Contextual self-checks for this prompt**
- Secondary modules still count toward prompt completeness. Do not leave them as TODOs.
- Diagnostic, backup, and observability surfaces materially affect acceptance credibility.
- Keep admin surfaces subject to the same authorization, masking, and audit rigor as the primary workflows.
- Make disaster-recovery and backup behavior operationally inspectable instead of documentation theater.

**Test-Authoring Instructions**
- Add frontend unit tests and frontend E2E coverage for health-dashboard rendering, roster-import result visibility, diagnostic export gating, backup/retention screens, read-only fallback banners, and admin configuration UX.
- Add backend unit and API/integration tests for import validation, alert-threshold evaluation, diagnostic export authorization, encrypted export metadata, backup-retention logic, restore-drill records, and read-only fallback triggers.
- Update the requirement-to-test mapping with these secondary but acceptance-critical modules.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `docs/design.md`, `docs/api-spec.md`, `repo/README.md`, and requirement traceability docs with the implemented operational modules.
- Keep `docs/questions.md` updated if any remaining deployment-policy ambiguity is still truly blocker-level.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not over-invest in cosmetic analytics that are not required by the prompt.
- Do not treat observability or backup behavior as screenshot-only features.
- Do not leave diagnostic export or backup controls unaudited.

**Completion Criteria**
- Remaining required observability, admin, backup, and recovery modules are implemented and documented.
- Tests exist for the major secondary risk areas.
- The product is functionally close to complete, not merely demo-complete.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 8 — Test suite authoring, endpoint coverage hardening, and requirement-to-test traceability

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Harden the repository’s authored test corpus so it is positioned to exceed 90% coverage on critical logic and survive a later static acceptance audit.

**Exact Scope**  
Focus on tests, `repo/run_tests.sh`, endpoint coverage inventory, and requirement-to-test traceability materials. Only make code refactors that are necessary to improve testability or isolate side effects.

**Implementation Instructions**
1. Complete frontend unit and E2E tests across:
   - auth/session and route guards;
   - dashboard rendering;
   - discussion creation/edit/report/moderate flows;
   - blocked-word handling and rewrite UX;
   - notifications center, preferences, unread counts, and bulk mark-as-read;
   - role-scoped academic actions;
   - order timelines, staffed-office or kiosk payment completion, printable receipts;
   - billing, penalties, refunds, reconciliation views;
   - observability/backup/admin screens;
   - offline cache/retry and read-only fallback behavior.
2. Complete backend unit tests across:
   - auth/security/lockout/token expiry;
   - scope resolution and authorization policies;
   - discussion/moderation/sensitive-word logic;
   - notification fan-out and category preference logic;
   - roster-import scope checks;
   - order lifecycle, auto-close timers, idempotency, taxes, bills, penalties, refunds, reversal entries, and reconciliation;
   - alert thresholds, backup retention, diagnostic export metadata, and read-only fallback triggers.
3. Complete backend API/integration tests across:
   - happy paths;
   - validation failures;
   - authorization failures;
   - not-found paths;
   - duplicate-submission/idempotency paths;
   - security-sensitive flows;
   - moderation/admin endpoints;
   - billing/refund/reconciliation endpoints;
   - diagnostics, backup, and roster-import endpoints.
4. Keep an explicit endpoint inventory using unique `METHOD + fully resolved PATH` entries. Mark coverage status for each endpoint and distinguish:
   - true no-mock HTTP/API coverage;
   - HTTP tests that still depend on mocks;
   - non-HTTP unit/integration coverage.
5. Add or update a requirement-to-test mapping artifact under `docs/` linking original-prompt requirements to exact frontend test files, backend unit test files, backend API/integration test files, and key assertions.
6. Create or finalize `repo/run_tests.sh` so it orchestrates frontend unit tests, frontend E2E tests, backend unit tests, and backend API/integration tests in a **docker-first** way. Keep it truthful to the real toolchain.
7. Make only small code refactors required for testability or side-effect isolation.

**Contextual self-checks for this prompt**
- Keep test instructions concrete, not generic. Do not just “add tests.”
- Maintain balanced frontend/backend coverage because this is a full-stack product.
- Do not over-count mocked HTTP tests as full endpoint coverage.
- Keep test intent statically understandable from the test code.
- Ensure test entry points, paths, and orchestration are inspectable and credible.

**Test-Authoring Instructions**
- This is the primary test-authoring prompt. Keep tests maintainable, specific, and strongly tied to real behavior.
- Keep frontend tests under `repo/frontend/unit_tests/` only, including authored E2E specs.
- Keep backend unit tests under `repo/backend/unit_tests/` only.
- Keep backend API/integration tests under `repo/backend/api_tests/` only.
- Explicitly record `Frontend unit tests: PRESENT` and `Frontend E2E tests: PRESENT` once direct file-level evidence exists. If direct file-level evidence is still missing, explicitly record `Frontend unit tests: MISSING` and treat that as a critical gap that must be closed before ending this prompt.
- Record the highest-risk uncovered modules, if any, and close them before ending this prompt.
- Ensure critical routes rely on true no-mock API coverage rather than mocked HTTP substitutes, and call out any remaining mock-heavy tests separately instead of counting them as acceptance-grade endpoint coverage.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Update `repo/README.md` with real test entry points, folder explanations, and docker-first test orchestration notes.
- Update the requirement-to-test mapping and endpoint inventory documents.
- Keep `docs/design.md` and `docs/api-spec.md` synchronized if tests reveal contract or module-boundary adjustments.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not add shallow snapshot-only tests that miss business rules.
- Do not place tests outside the required folders.
- Do not leave `repo/run_tests.sh` inconsistent with the actual test setup.

**Completion Criteria**
- Test coverage is authored broadly enough to credibly exceed 90% on critical logic once executed later.
- Endpoint coverage intent, no-mock vs mocked test classification, and requirement-to-test traceability are inspectable.
- `repo/run_tests.sh` exists and truthfully orchestrates the test suites.
- Docs reflect the actual test structure.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 9 — Dockerization, config hardening, and documentation synchronization

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Finalize container assets, runtime configuration discipline, deployment notes, and cross-document synchronization so the repo is ready for later execution without hidden setup assumptions.

**Exact Scope**  
Work on Dockerfiles, docker-compose, runtime config loading, local-network certificate handling, service names/ports, startup docs, and final README/design/api-spec synchronization. Do not run Docker. Do not run tests.

**Implementation Instructions**
1. Finalize `repo/frontend/Dockerfile` and `repo/backend/Dockerfile` so all build/runtime dependencies are explicitly declared.
2. Finalize `repo/docker-compose.yml` with the minimal justified service set needed for later verification:
   - frontend;
   - backend;
   - MySQL;
   - and only additional services if genuinely required by implemented behavior.
3. Serve the frontend as a built Vue app without adding an API reverse-proxy layer unless the implementation truly requires it and the prompt justifies it.
4. Make the backend serve its own HTTPS-enabled local-network API path directly or through an implementation that is clearly justified and fully documented. Keep certificate assumptions honest and local-only.
5. Ensure ports, service names, startup commands, health references, and verification steps match exactly across:
   - frontend config;
   - backend config;
   - Dockerfiles;
   - docker-compose;
   - README;
   - API docs;
   - diagnostics/health docs.
6. Harden config handling:
   - no absolute paths;
   - no host-only packages or hidden global tools;
   - no undocumented env vars;
   - sane defaults for offline district-network operation;
   - truthful handling of local certificates, token secrets, backup paths, and diagnostic export paths.
7. Update README so it includes only real information:
   - project overview;
   - exact stack;
   - repo structure;
   - startup command(s);
   - services and ports;
   - config notes;
   - verification method;
   - test entry points;
   - reviewer-usable authentication and role/access details needed for verification when authentication exists;
   - offline/LAN/local-payment constraints;
   - certificate, token, and backup-key assumptions documented honestly.
8. Perform a static cross-check for Docker/README/config alignment and fix mismatches immediately.

**Contextual self-checks for this prompt**
- README authenticity is pass/fail critical.
- Port spoofing, hidden dependencies, and undocumented setup assumptions are acceptance blockers.
- Keep the container story minimal, explicit, and host-independent.
- Keep test instructions aligned to the real Dockerized structure.

**Test-Authoring Instructions**
- Add or update tests for config parsing, startup config defaults, Docker-facing env handling, certificate/config validation helpers, and any deployment/runtime code paths introduced here.
- Update coverage mapping for configuration-sensitive endpoints or services if needed.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Synchronize `repo/README.md`, `docs/design.md`, `docs/api-spec.md`, endpoint inventory, and requirement-to-test traceability with the finalized container/config setup.
- Make sure docs describe only real services, ports, commands, and verification steps.
- Keep `docs/questions.md` honest about unresolved certificate or deployment assumptions.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not introduce unnecessary sidecars or reverse proxies.
- Do not leave service-name or port mismatches anywhere.
- Do not promise fully automated certificate provisioning if the repo does not truly implement it.

**Completion Criteria**
- Docker assets are explicit, minimal, and internally consistent.
- Runtime/deployment assumptions are documented honestly.
- README, API spec, design doc, code, and config agree on services, commands, and ports.
- The repo is statically ready for later `docker compose up` verification.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`

## Prompt 10 — Final static readiness audit before execution

Only do the scope of this prompt. Do not pre-implement future prompts.
Maintain requirement-to-module and requirement-to-test traceability.
Do not try to build the whole project. Laser focus on the current prompt.

**Objective**  
Perform a rigorous static completeness and consistency sweep so the repository is ready for later execution, self-test, and independent review without hidden surprises.

**Exact Scope**  
Audit the existing code, docs, tests, configs, Docker assets, and deployment assumptions. Fix only the gaps needed for completeness, honesty, and traceability. Do not run Docker, do not run tests, and do not add unrelated extra features.

**Implementation Instructions**
1. Perform a full original-prompt requirement audit and confirm every explicit requirement is accounted for in code, config, docs, or an honestly documented assumption in `docs/questions.md`.
2. Verify strict repo-structure compliance:
   - `docs/questions.md` exists in the correct location;
   - `repo/README.md`, `repo/docker-compose.yml`, `repo/run_tests.sh`, `repo/frontend/Dockerfile`, and `repo/backend/Dockerfile` exist;
   - `repo/frontend/unit_tests/`, `repo/backend/unit_tests/`, and `repo/backend/api_tests/` exist;
   - `sessions/` remains untouched;
   - no root-level forbidden test folders exist.
3. Perform a static security audit over:
   - auth entrypoints;
   - lockout and token-expiry handling;
   - route, function, object, and scope authorization boundaries;
   - idempotency protection for payment completion and related retry-prone endpoints;
   - masking/default data exposure rules;
   - audit immutability;
   - diagnostic/export/download restrictions;
   - secret/log leakage prevention.
4. Perform a static business-logic audit over:
   - dashboard role separation;
   - discussion/Q&A/comment/report flows;
   - edit-window and moderation behavior;
   - sensitive-word filtering and rewrite enforcement;
   - notification categories, unread counts, and bulk-read behavior;
   - teacher/registrar scoped operations;
   - order states, local payment completion, auto-close, receipt generation, and redemption;
   - billing schedule, tax, penalties, refunds, reversal entries, reconciliation, and end-of-day closeout support;
   - observability thresholds, circuit breaking, read-only cached views, diagnostics, backups, retention, and restore-runbook visibility.
5. Perform a static test-readiness audit:
   - confirm major requirements map to authored tests;
   - confirm endpoint inventory is current;
   - confirm true no-mock API coverage is called out separately from mocked coverage;
   - confirm frontend unit tests and frontend E2E tests are present by direct file evidence;
   - confirm happy paths, failure paths, security paths, and user-visible failure states are covered;
   - confirm `repo/run_tests.sh` targets the real test locations and docker-first tooling.
6. Perform a final static Docker/README/config audit:
   - service names;
   - exposed ports;
   - startup commands;
   - verification steps;
   - reviewer-usable authentication and role/access details when authentication exists;
   - host-only dependency risks;
   - undocumented env var risks;
   - port spoofing or mismatch risks;
   - documentation honesty.
7. Tighten any remaining code/doc/config mismatches discovered in this audit.
8. Update `docs/questions.md` with any final unresolved but material ambiguity. Do not leave hidden assumptions undocumented.
9. Refresh `repo/README.md`, `docs/design.md`, `docs/api-spec.md`, the endpoint inventory, and the requirement-to-test mapping so they match the final static state exactly.
10. After finishing the static sweep, output a short final note to the user explicitly telling them to invoke the separate execution-plan reviewer workflow for an independent post-plan review before beginning execution.

**Contextual self-checks for this prompt**
- Treat this prompt like a near-self-test pass shaped by the static acceptance audit.
- Support every strong completion claim with static evidence in the repository.
- Treat Docker/README/test-placement/security-boundary mismatches as acceptance-critical.
- Keep unresolved gaps honest instead of burying them.

**Test-Authoring Instructions**
- Add only the missing high-priority tests discovered during the static audit.
- Close any critical frontend/backend balance gaps in the authored coverage.
- Update endpoint and requirement traceability if tests move or expand.
- Do **not** run tests yet.

**Documentation Update Instructions**
- Refresh `repo/README.md`, `docs/design.md`, `docs/api-spec.md`, endpoint inventory, requirement-to-test mapping, and `docs/questions.md` so they match the final static state exactly.
- Make the repository easy for a reviewer to inspect quickly and accurately.

**Explicit Constraints / What Not To Do**
- Do not run Docker yet.
- Do not run tests yet.
- Do not add optional features that are not required by the original prompt.
- Do not leave undocumented assumptions, port mismatches, coverage gaps, or traceability holes.
- Do not touch `sessions/` or create session-style artifacts.
- Do not attempt the reviewer workflow inside this prompt.

**Completion Criteria**
- Every explicit original-prompt requirement is either implemented or honestly captured as an external-input assumption in `docs/questions.md`.
- Repo structure, docs, endpoint inventory, tests, Docker assets, and configs are statically consistent.
- Security-sensitive areas and requirement-to-test coverage are easy to inspect.
- The repository is ready for later execution and independent review without hidden manual steps.

**Return at the end of this prompt**
- `files created/updated`
- `requirement coverage completed`
- `deferred items`
- `docs updated`
- `test files added`
- `any assumptions added to questions.md`
