# Ambiguity Log — CampusLearn LAN Portal

This file is the sole ambiguity log for the project. Every unresolved assumption or implementation-shaping interpretation is recorded here. Entries follow the format: **The Gap** / **The Interpretation** / **Proposed Implementation**.

---

## 1
### The Gap
The prompt requires multi-role accounts with fine-grained permissions down to term, course, class section, and specific grade items, but it does not define how overlapping grants across multiple roles should resolve when one account is both a teacher and a registrar or administrator.

### The Interpretation
Allow one account to hold multiple active role assignments at the same time. Resolve access through the union of granted scopes for ordinary work, while still reserving explicitly administrator-only operations for administrator-scoped assignments. Do not invent a generalized deny-rule system unless it becomes necessary.

### Proposed Implementation
Model role assignments separately from users and store scope records for term, course, section, and grade-item boundaries. Evaluate permissions through Laravel Policies and scope-aware service guards that combine all active grants on the account. Keep admin-only capabilities behind explicit capability checks rather than implied role unions.

---

## 2
### The Gap
The prompt includes course announcements and threaded discussion boards with comments, Q&A, `@mentions`, reporting, and staff moderation, but it does not define whether announcements and discussion threads are separate entities or one shared threaded content model with different posting modes.

### The Interpretation
Use one shared threaded-content domain with a thread type and posting-mode metadata so announcements and open discussions can share comments, reports, mentions, moderation, and audit behavior while still rendering differently in the UI.

### Proposed Implementation
Create thread, post, comment, mention, report, and moderation-action entities. Add fields for thread type (`announcement` or `discussion`), Q&A enablement, lock status, hidden status, and authoring permissions. Keep moderation and reporting generic across both thread types.

---

## 3
### The Gap
The prompt says users can edit within 15 minutes, but it does not clarify whether the 15-minute rule applies to all roles, only students, or whether moderation/administrative edits are exempt.

### The Interpretation
Apply the 15-minute self-edit limit to normal author edits on posts/comments. Allow authorized staff moderators and administrators to edit or moderate outside that window when policy permits, while recording an audit event for those elevated edits.

### Proposed Implementation
Store created-at timestamps and author ownership on editable content. Enforce the 15-minute window in domain services and policies for self-edits. Allow privileged moderation or administrative corrections through separate abilities and record those actions in audit logs.

---

## 4
### The Gap
Sensitive-word filtering must run at submit time and highlight blocked terms, but the prompt does not define who manages the blocked-term list, how matching should behave, or whether blocked content should ever be stored.

### The Interpretation
Treat sensitive-word rules as a locally managed administrative configuration. Match case-insensitively against normalized text and prevent persistence of blocked content in published tables. Return a validation-style payload that identifies blocked terms and text ranges for UI highlighting.

### Proposed Implementation
Create an admin-managed sensitive-word rule set stored in MySQL. Normalize incoming text before validation. Reject the submission before publish when matches are found. Return a structured error payload containing matched terms and range metadata. Log the validation event without storing the rejected full raw content unless a masked/hashed diagnostic record is truly necessary.

---

## 5
### The Gap
Notification Center alerts include appointment changes, but the prompt does not otherwise define an appointment domain or whether appointments are separate from facility rentals, registrar meetings, or scheduled services.

### The Interpretation
Treat appointments as scheduled service/reservation records that can cover facility-rental time slots and other staff-managed scheduled interactions that need change notifications. Keep the model minimal and only as deep as needed to truthfully drive appointment-change alerts.

### Proposed Implementation
Add an appointment/reservation entity with status, scheduled time window, linked student/user, linked service or facility context, and notification hooks for create/reschedule/cancel actions. Use it for facility-rental scheduling first, with room for registrar-managed appointment expansion later.

---

## 6
### The Gap
The prompt requires local payment-like steps handled at a staffed office or kiosk workflow, but it does not define whether orders are completed by cash, card-on-local-terminal, waiver posting, or a generic staff-confirmed payment result.

### The Interpretation
Model the payment workflow as a local settlement confirmation process that can record payment method metadata without integrating external processors. Treat office and kiosk completion as staff-controlled local transaction finalization flows with idempotent retries.

### Proposed Implementation
Create payment-attempt and settlement records that capture local method type, staff/kiosk operator context, timestamps, receipt numbers, and idempotency keys. Mark orders paid only after a successful local completion event is recorded. Generate printable receipts from those records.

---

## 7
### The Gap
The prompt requires idempotent callback-style completion endpoints to prevent double-posting when staff retries a transaction, but it does not define who is calling those endpoints or whether callbacks are external, kiosk-internal, or office-terminal retries.

### The Interpretation
Treat callback-style completion endpoints as internal retry-safe settlement endpoints used by the kiosk UI, staffed office UI, or local terminal integrations inside the district network. Do not model them as internet-facing third-party webhooks.

### Proposed Implementation
Expose authenticated internal completion endpoints that require idempotency keys and replay-safe request handling. Store idempotency records keyed by action type plus order/payment context. Return the previously finalized result on safe retries.

---

## 8
### The Gap
The prompt requires configurable local sales tax rules per fee category and monetary values stored in cents, but it does not specify tax rounding behavior, whether tax is included or added on top, or how category changes affect existing open bills.

### The Interpretation
Apply tax as an additive amount on top of the pre-tax line amount unless an item explicitly marks itself tax-inclusive later. Round at the line level to cents using a consistent financial rounding rule. Freeze the effective tax snapshot on each posted charge so later category rule changes do not mutate historical bills.

### Proposed Implementation
Store fee-category tax rules with effective-dated versions. When generating a charge, persist the applied tax-rule version, taxable base, computed tax amount, and resulting gross amount in cents. Use the stored snapshot for refunds, statements, and reconciliation.

---

## 9
### The Gap
Billing supports initial, recurring, supplemental, and penalty charges, but the prompt does not define the source event for initial bill generation or the recurrence stop conditions for monthly billing.

### The Interpretation
Generate initial bills from the creation or activation of an eligible fee-bearing enrollment/service/order. Continue recurring billing monthly until the linked billing schedule reaches its configured end date, is manually closed, or the underlying enrollment/service is terminated.

### Proposed Implementation
Create bill schedules tied to enrollments, services, or fee-based items. Support one-time, recurring, supplemental, and penalty schedule types. Run the recurring scheduler at 2:00 AM on the 1st. Stop generation when the schedule is closed, exhausted, or detached from an active source record.

---

## 10
### The Gap
Refunds require partial amounts and reason codes, but the prompt does not define approval routing, whether refunds can exceed available paid balances after previous partial refunds, or how redeemed orders should behave when refunded.

### The Interpretation
Limit refundable amounts to the remaining paid-and-not-yet-refunded balance. Require an approved reason code and an authorized operator. Allow refunds on redeemed orders only when business rules explicitly permit reversal, and record the redeemed-status impact separately.

### Proposed Implementation
Store cumulative paid, redeemed, and refunded amounts on the order/bill context. Enforce refund ceilings in a refund domain service. Require authorized refund actors and reason codes. Generate reversal ledger entries and reconciliation flags on every approved refund. Add a rule hook for redeemed-order reversal handling.

---

## 11
### The Gap
Observability requires health dashboards for database/queue status, alert thresholds, circuit breaking, and fallback to read-only cached views, but the prompt does not define which user-facing screens must remain readable during a read-only fallback period.

### The Interpretation
Keep the most important informational surfaces readable from cached data during read-only fallback: dashboards, course/announcement/discussion reads, notification reads, order history, billing history, and admin health views. Disable write actions clearly while the system is in protected mode.

### Proposed Implementation
Add a client-readable health/circuit-break endpoint plus cached read-model stores in IndexedDB. Render visible banners and disable or queue write actions when the backend reports read-only fallback mode. Keep cached timestamps and staleness indicators visible.

---

## 12
### The Gap
The prompt requires poor-network caching on the client, automatic retries with exponential backoff, and local message queues for notification fan-out, but it does not define which operations are safe to auto-retry versus which need explicit user conflict resolution.

### The Interpretation
Auto-retry only idempotent or client-owned safe operations such as notification preference updates, non-destructive fetch refreshes, and retry-safe submission stages with idempotency keys. Require explicit conflict handling for content edits, moderation actions, roster imports, and payment completion if the original result cannot be safely confirmed.

### Proposed Implementation
Classify client actions into safe auto-retry, queued retry-with-idempotency, and manual-conflict-resolution groups. Persist queued retry metadata in IndexedDB. Present conflict prompts for ambiguous completion states instead of silently replaying sensitive actions.

---

## 13
### The Gap
The prompt requires diagnostic exports to an encrypted local file plus nightly encrypted backups with 30-day retention, but it does not define key-management expectations or whether the same key material should be reused for both diagnostics and backups.

### The Interpretation
Use separately configured local encryption secrets for backup archives and diagnostic exports. Keep key provisioning as a documented local deployment responsibility rather than inventing secret-management infrastructure outside the prompt.

### Proposed Implementation
Add independent configuration entries for backup-encryption and diagnostic-export encryption, document them in `docs/questions.md`, and keep them referenced in deployment docs and restore-runbook materials. Store only encrypted file metadata and paths in MySQL.

---

## 14
### The Gap
The prompt requires a documented restore runbook with quarterly disaster recovery drills on offline hardware, but it does not define how much of the drill process must be implemented in the product versus documented as operational procedure.

### The Interpretation
Implement drill and restore records, backup history, and restore-runbook visibility inside the product, but keep the physical restore execution steps documented as an operational procedure rather than trying to automate the entire hardware recovery process.

### Proposed Implementation
Create admin-visible backup history, restore-runbook documents, and disaster-recovery drill log records. Track drill dates, operators, outcomes, and notes in the system. Keep the runbook document under `docs/` and surface it in the admin UI as a reference artifact.

---

## 15
### The Gap
The prompt states "penalty charges 5% after 10 days past due" but does not specify whether a penalty itself accrues further penalties if its own balance goes past due (i.e., whether penalty bills compound).

### The Interpretation
Treat penalty bills as first-class bills with their own `due_on` and `status`. A penalty bill may itself age past due and incur a further penalty — `PenaltyCalculator` is invariant over `type`. The `penalty_jobs.idempotency_key` (unique) prevents double-application for a given `(bill, run_date)` pair.

### Proposed Implementation
`BillType` enum explicitly includes `Penalty`, and `bills.type = 'penalty'` rows are eligible for `PenaltyJob` scanning. Confirmation with the business owner recorded here; if they want non-compounding penalties, a later prompt can exclude `type = penalty` bills from the scheduler query without schema change.

---

## 16
### The Gap
The prompt requires a submit-time sensitive-word filter but does not specify whether rules are per-language or apply globally across all content languages.

### The Interpretation
Treat `sensitive_word_rules.pattern` as language-agnostic by storing patterns in NFC-normalized, lowercased form. `SensitiveWordFilter` normalizes the inbound body identically before matching. No per-language column is added in Prompt 2.

### Proposed Implementation
If, in later phases, rules must vary by locale, add `sensitive_word_rules.locale` (nullable, indexed) and filter by `users.locale` at inspection time. Schema migration would be additive, with `null` locale treated as global. Recorded here so future work does not redesign the table.

---

## 17
### The Gap
The prompt requires idempotent completion endpoints but does not specify the conflict semantics when the same `Idempotency-Key` is replayed with a different request body.

### The Interpretation
`IdempotencyService` canonicalizes the request body and stores `sha256(canonical_json)` as `request_fingerprint`. On replay with a matching key but differing fingerprint, it raises `IdempotencyReplay` which the `ApiExceptionRenderer` maps to 409 `IDEMPOTENCY_KEY_CONFLICT`. Matching fingerprints return the cached `(status, body)` verbatim with `X-Idempotent-Replay: true`.

### Proposed Implementation
Documented in `docs/api-spec.md` §10. No further work required until per-endpoint form-request shapes land in Prompt 3 controllers.

---

## 18
### The Gap
`POST /api/v1/auth/logout` revokes the current Sanctum token, but the prompt does not specify whether logout should revoke all tokens for the user (all devices) or only the token used for the current request.

### The Interpretation
Revoke only the token used for the current request. This matches the expected behavior when a user logs out from one device without terminating sessions on other devices. An explicit "logout all devices" capability can be added later if required.

### Proposed Implementation
`AuthService::logout` calls `$user->currentAccessToken()->delete()`. An `admin`-gated `POST /api/v1/auth/logout-all` endpoint (Prompt 4+) can call `$user->tokens()->delete()` for the multi-device case.

---

## 19
### The Gap
The frontend auth store holds the session token in `localStorage`, but the prompt does not define cross-tab session expiry behavior — if token expires in one tab, other open tabs may continue to show stale auth state until they navigate.

### The Interpretation
Accept eventual consistency across tabs for the LAN-only offline environment. Each tab checks `isSessionExpired` on navigation and on the global Axios 401 interceptor. Real-time cross-tab sync (via BroadcastChannel or StorageEvent) is not required in the base implementation; add it later if the district feedback indicates it is needed.

### Proposed Implementation
The router guard evaluates `auth.isSessionExpired` on every navigation. The Axios 401 interceptor clears localStorage and redirects immediately. No BroadcastChannel wiring in Prompt 3; this entry records the tradeoff for future review.

---

## 20
### The Gap
The `@mention` feature requires parsing handles from post/comment bodies, but the prompt does not define the exact character grammar for a valid handle.

### The Interpretation
A handle is the local-part of the user's email (the substring before `@`). Valid handle characters are `[A-Za-z0-9._-]` with a required leading alphanumeric and a maximum of 64 characters. The `MentionParser` resolves handles by doing a case-insensitive match against the email local-part stored in the `users` table.

### Proposed Implementation
`MentionParser` uses the regex `/(?<![A-Za-z0-9._-])@([A-Za-z0-9][A-Za-z0-9._-]{0,63})/u`. `ContentSubmissionService::processMentions` queries users via `WHERE LOWER(email) LIKE '{handle}@%'` for each distinct handle extracted.

---

## 21
### The Gap
The notification fan-out dispatches jobs to a queue but the prompt does not specify batch size or how large recipient sets should be chunked.

### The Interpretation
Chunk recipient lists by a configurable batch size before dispatching `SendNotificationJob`. Default to 50 recipients per job dispatch. This bounds memory use per job and allows the queue worker to handle one batch at a time.

### Proposed Implementation
`NotificationOrchestrator::notify` chunks the effective recipient list using `config('campuslearn.notifications.fanout_batch_size', 50)` and dispatches one `SendNotificationJob` per chunk via `Bus::dispatch`. The config key is `CL_NOTIFICATION_FANOUT_BATCH_SIZE`.

---

## 22
### The Gap
The penalty billing rule specifies "5% after 10 days past due" but does not define the due date on the generated penalty bill or whether penalty bills themselves compound.

### The Interpretation
A penalty bill is issued on the day the penalty job runs. Its due date is `issued_on + penalty_bill_due_days` days (default 30). Penalty bills are themselves eligible for compounding: the `PenaltyJob` evaluates all bills with status in `(open, partial, past_due)`, which includes previously issued penalty bills whose own `due_on` has passed the grace period.

### Proposed Implementation
`BillingService::applyPenalty` sets `due_on = now()->addDays(config('campuslearn.billing.penalty_bill_due_days', 30))`. The `PenaltyJob` calls `Bill::pastDue()->each(...)` which uses the `scopePastDue` scope on `Bill` to include all eligible bill types. Idempotency is enforced via `penalty_jobs.idempotency_key = sha256(bill_id:run_date)`.

---

## 23
### The Gap
The moderation router path convention was ambiguous: early docs used `/api/v1/moderation/*` while the frontend adapter and tests were written with admin-prefixed action-specific paths (`/api/v1/admin/threads/{id}/hide`, etc.).

### The Interpretation
Use the admin-prefixed action-specific routes because the frontend adapter (`src/adapters/moderation.ts`) and all API tests (`api_tests/Domain/Moderation/`) agree on those paths. The `/moderation/` prefix was an early design artifact and was superseded.

### Proposed Implementation
Routes follow the admin-prefix convention: `GET /admin/moderation/queue`, `GET /admin/moderation/history`, and action routes `POST /admin/threads/{id}/hide|restore|lock`, `POST /admin/threads/{threadId}/posts/{id}/hide|restore`. `ModerationController` has dedicated action methods using `Request` (not `ModerationActionRequest`) so the action type is derived from the route rather than the request body.

---

## 24
### The Gap
The audit flagged that `GET /terms`, `GET /courses`, and `GET /sections` return broad lists without per-user scope filtering, raising a concern about over-broad data visibility.

### The Interpretation
These are academic catalog endpoints. Scope enforcement follows strict least-privilege with three tiers and explicit denial: (1) admin and registrar — unrestricted (see all records including archived); (2) users with active teacher grants (course/section-scoped) — restricted to their granted scope; (3) users with active enrollments — restricted to their enrolled scope. Any user that matches none of these tiers receives an empty list (strict denial — no status-based fallback). Section roster (`GET /sections/{id}/roster`) retains its `SectionPolicy::viewRoster` object-level check.

### Proposed Implementation
Each controller exposes a single `resolveAllowed*Ids(User $user): ?array` method. Return value contract: `null` = admin/registrar, unrestricted query; non-empty array = allowed ID set; empty array `[]` = strict denial. Laravel's `whereIn('id', [])` renders as `0 = 1`, producing zero rows. Tier 2 traverses teacher `role_assignments` with `scope_type IN (course, section)` and resolves section grants upward to course/term IDs. Tier 3 traverses `enrollments WHERE status = 'enrolled'` and resolves upward via section→course→term. Teachers with only global-scope assignments (no explicit course/section grants) and users with no active enrollments both receive the empty-array denial.
