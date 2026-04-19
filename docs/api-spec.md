# CampusLearn — API Specification

## 1. Base URL and Versioning

All API endpoints are prefixed with `/api/v1`. The backend runs on port `8000` in development and is reverse-proxied by nginx in production.

```
Base: http://<lan-host>:8000/api/v1
```

**Routing in docker compose:**
Browser JavaScript makes requests to `/api/v1/...` which resolve to `http://localhost:5173/api/v1/...` (the `frontend` nginx service on port 5173). The frontend nginx `location /api` block proxies these requests to `http://backend:8000/api/...` inside the docker network. The backend never receives requests directly from the browser in the standard docker compose setup.

**Local development (outside docker):**
When running `npm run dev`, Vite's dev server proxies `/api/...` to `http://localhost:8000` via the `server.proxy` config in `vite.config.ts`. Direct browser access to `http://localhost:8000/api/v1/...` also works for tooling such as Postman or curl.

---

## 2. Request / Response Envelope

### Success Response
```json
{
  "data": { ... } | [ ... ],
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 200,
    "last_page": 8
  }
}
```
`meta` is omitted for non-paginated responses.

### Error Response
```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Human-readable summary.",
    "details": {
      "field_name": ["Validation message."]
    }
  }
}
```

### Sensitive-Word Rejection (422)
```json
{
  "error": {
    "code": "SENSITIVE_WORDS_BLOCKED",
    "message": "Your submission contains blocked terms. Please revise before publishing.",
    "blocked_terms": [
      { "term": "badword", "start": 14, "end": 21 }
    ]
  }
}
```

---

## 3. Authentication Strategy

- **Token type:** Laravel Sanctum bearer tokens (short-lived, stored in browser `localStorage`).
- **Header:** `Authorization: Bearer <token>`
- **Token expiry:** Configurable via `CL_TOKEN_TTL_MINUTES` (default: 720 minutes / 12 hours). Wired through `config/campuslearn.php` (`auth.token_ttl_minutes`) into `AuthService`. The `SANCTUM_TOKEN_EXPIRY` env var is not used.
- **Refresh:** No token-refresh endpoint exists. Clients re-authenticate via POST `/api/v1/auth/login` when the token expires.
- **Lockout:** After 5 failed login attempts within 15 minutes, the account is locked for 15 minutes.
- **Logout:** POST `/api/v1/auth/logout` revokes the current token server-side.
- **Current user:** GET `/api/v1/auth/me` returns the authenticated user's profile and roles.
- **Note:** There is no token refresh endpoint. Tokens are re-issued at login.

---

## 4. Idempotency-Key Convention

Payment completion, billing generation, and refund endpoints require an `Idempotency-Key` header:

```
Idempotency-Key: <uuid-v4>
```

The server stores the key with the result for 24 hours. Replaying the same key returns the original result with `X-Idempotent-Replay: true` header. Keys are scoped per endpoint + resource to prevent cross-resource collisions.

---

## 5. Pagination, Filter, and Sort Conventions

### Pagination
```
GET /api/v1/resource?page=1&per_page=25
```

### Filtering
```
GET /api/v1/resource?filter[field]=value&filter[status]=active
```

### Sorting
```
GET /api/v1/resource?sort=created_at&direction=desc
```

---

## 6. Error Code Reference

| HTTP Status | Code | Meaning |
|---|---|---|
| 400 | `BAD_REQUEST` | Malformed request body or query |
| 401 | `UNAUTHENTICATED` | Missing or invalid bearer token |
| 403 | `FORBIDDEN` | Authenticated but not authorized for this action/resource |
| 404 | `NOT_FOUND` | Resource does not exist |
| 409 | `CONFLICT` | Duplicate submission or state conflict |
| 410 | `GONE` | Resource permanently removed (e.g., deleted post) |
| 422 | `VALIDATION_FAILED` | Input validation error |
| 422 | `SENSITIVE_WORDS_BLOCKED` | Submission blocked by sensitive-word filter |
| 422 | `EDIT_WINDOW_EXPIRED` | Self-edit attempted after 15-minute window |
| 423 | `ACCOUNT_LOCKED` | Account locked due to failed login attempts |
| 429 | `RATE_LIMITED` | Too many requests |
| 503 | `SERVICE_UNAVAILABLE` | Circuit breaker open; read-only mode active |

---

## 7. Endpoint Inventory (Skeleton)

All endpoints are authenticated unless marked `[public]`. Endpoints requiring an `Idempotency-Key` are marked `[idem]`.

### 7.1 Auth / Session
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/auth/login` | [public] Authenticate, issue token |
| POST | `/api/v1/auth/logout` | Logout, revoke current token |
| GET | `/api/v1/auth/me` | Current user profile + roles |

### 7.2 Health / System
| Method | Path | Description |
|---|---|---|
| GET | `/api/health` | [public] Service health check (DB, queue, scheduler) |
| GET | `/api/v1/health/circuit` | Circuit breaker state + read-only mode flag |
| GET | `/api/v1/health/metrics` | Request metrics summary (admin only) |

### 7.3 Terms and Courses
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/terms` | List terms |
| GET | `/api/v1/terms/{id}` | Get term detail |
| GET | `/api/v1/courses` | List courses (scoped to user's enrollments/assignments) |
| GET | `/api/v1/courses/{id}` | Get course detail |
| GET | `/api/v1/sections` | List sections |
| GET | `/api/v1/sections/{id}` | Get section detail |
| GET | `/api/v1/sections/{id}/roster` | List enrolled students for section (staff) |

### 7.4 Roster Import
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/terms/{termId}/roster-imports` | Import roster CSV for term (registrar, scoped) |
| GET | `/api/v1/terms/{termId}/roster-imports` | Import history for term |
| GET | `/api/v1/roster-imports/{importId}` | Import job status and error detail |

### 7.7 Grade Items
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/sections/{sectionId}/grade-items` | List grade items for section |
| POST | `/api/v1/sections/{sectionId}/grade-items` | Create grade item (teacher, scoped) |
| PATCH | `/api/v1/sections/{sectionId}/grade-items/{itemId}` | Update grade item |
| POST | `/api/v1/sections/{sectionId}/grade-items/{itemId}/publish` | Publish grade item (triggers notifications) |

### 7.8 Threads (Announcements and Discussions)
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/threads` | List threads (optional `?section_id=` filter) |
| POST | `/api/v1/threads` | Create thread (runs sensitive-word filter) |
| GET | `/api/v1/threads/{id}` | Get thread with posts |
| PATCH | `/api/v1/threads/{id}` | Update thread (author within 15 min, or mod) |

### 7.9 Posts and Comments
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/threads/{threadId}/posts` | List posts for thread |
| POST | `/api/v1/threads/{threadId}/posts` | Create post (runs sensitive-word filter) |
| GET | `/api/v1/threads/{threadId}/posts/{id}` | Get post with comments |
| PATCH | `/api/v1/threads/{threadId}/posts/{id}` | Edit post (author within 15 min) |
| DELETE | `/api/v1/threads/{threadId}/posts/{id}` | Delete own post (within window) |
| POST | `/api/v1/posts/{postId}/comments` | Add comment |
| PATCH | `/api/v1/posts/{postId}/comments/{id}` | Edit comment (author within 15 min) |
| DELETE | `/api/v1/posts/{postId}/comments/{id}` | Delete own comment |
| POST | `/api/v1/posts/{postId}/reports` | Report post |
| POST | `/api/v1/posts/{postId}/comments/{commentId}/reports` | Report comment |

### 7.10 Mentions
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/mentions` | List @mentions for current user |

### 7.11 Moderation
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/admin/moderation/queue` | List threads in moderation queue (admin) |
| GET | `/api/v1/admin/moderation/history` | Moderation action audit log (admin) |
| POST | `/api/v1/admin/threads/{id}/hide` | Hide thread (admin) |
| POST | `/api/v1/admin/threads/{id}/restore` | Restore hidden thread (admin) |
| POST | `/api/v1/admin/threads/{id}/lock` | Lock thread (admin) |
| POST | `/api/v1/admin/threads/{threadId}/posts/{id}/hide` | Hide post (admin) |
| POST | `/api/v1/admin/threads/{threadId}/posts/{id}/restore` | Restore hidden post (admin) |

### 7.12 Admin — Sensitive-Word Rules
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/sensitive-words/check` | Check body text against active rules (pre-submission) |
| GET | `/api/v1/admin/sensitive-words` | List sensitive-word rules (admin) |
| POST | `/api/v1/admin/sensitive-words` | Add rule (admin) |
| DELETE | `/api/v1/admin/sensitive-words/{id}` | Remove rule (admin) |

### 7.13 Notifications
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/notifications` | List notifications for current user |
| GET | `/api/v1/notifications/unread-count` | Unread counts per category |
| POST | `/api/v1/notifications/mark-read` | Bulk mark notifications as read |
| POST | `/api/v1/notifications/{id}/read` | Mark single notification as read |
| GET | `/api/v1/notifications/preferences` | Get subscription preferences |
| PATCH | `/api/v1/notifications/preferences` | Update subscription preferences |

### 7.14 Orders
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/orders` | List orders for current user |
| POST | `/api/v1/orders` | Place order for fee-based service |
| GET | `/api/v1/orders/{id}` | Get order detail + timeline |
| DELETE | `/api/v1/orders/{id}` | Cancel pending order |
| GET | `/api/v1/orders/{id}/timeline` | Get order status history |

### 7.15 Payment Completion
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/orders/{orderId}/payment` [idem] | Initiate payment attempt (kiosk/office) |
| POST | `/api/v1/orders/{orderId}/payment/complete` [idem] | Complete payment (idempotent) |
| GET | `/api/v1/orders/{orderId}/receipt` | Get receipt for paid order |
| GET | `/api/v1/orders/{orderId}/receipt/print` | Printable receipt view |

### 7.16 Catalog and Fee Categories
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/catalog` | List available fee-based services/items |
| POST | `/api/v1/admin/catalog` | Create catalog item (admin) |
| PATCH | `/api/v1/admin/catalog/{id}` | Update catalog item (admin) |
| GET | `/api/v1/admin/fee-categories` | List fee categories |
| POST | `/api/v1/admin/fee-categories` | Create fee category (admin) |
| PATCH | `/api/v1/admin/fee-categories/{id}` | Update fee category / tax rule (admin) |

### 7.17 Bills and Billing Schedules
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/bills` | List bills for current user |
| GET | `/api/v1/bills/{id}` | Get bill detail + line items |
| GET | `/api/v1/admin/bills` | List all bills (admin/registrar) |
| POST | `/api/v1/admin/bills/generate` [idem] | Manually trigger bill generation (admin) |
| GET | `/api/v1/billing-schedules` | List billing schedules |
| PATCH | `/api/v1/billing-schedules/{id}` | Update/close billing schedule (admin) |

### 7.18 Refunds
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/bills/{billId}/refunds` [idem] | Request refund (admin/registrar) |
| GET | `/api/v1/refunds` | List refunds (current user or admin) |
| GET | `/api/v1/refunds/{id}` | Get refund detail |
| GET | `/api/v1/refund-reason-codes` | List valid refund reason codes |

### 7.19 Ledger and Reconciliation
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/admin/ledger` | List ledger entries (admin, filterable) |
| GET | `/api/v1/admin/reconciliation` | List reconciliation flags |
| POST | `/api/v1/admin/reconciliation/{id}/resolve` | Mark reconciliation flag resolved |
| GET | `/api/v1/admin/reconciliation/summary` | End-of-day closeout summary |

### 7.20 Appointments
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/appointments` | List appointments for current user |
| POST | `/api/v1/appointments` | Create appointment (staff/admin) |
| GET | `/api/v1/appointments/{id}` | Get appointment detail |
| PATCH | `/api/v1/appointments/{id}` | Reschedule appointment |
| DELETE | `/api/v1/appointments/{id}` | Cancel appointment |

### 7.21 Diagnostics and Backups
| Method | Path | Description |
|---|---|---|
| POST | `/api/v1/admin/diagnostics/export` [idem] | Trigger encrypted diagnostic export |
| GET | `/api/v1/admin/diagnostics/exports` | List diagnostic export records |
| GET | `/api/v1/admin/backups` | List backup records (admin) |
| POST | `/api/v1/admin/backups/trigger` [idem] | Trigger manual backup (admin) |
| GET | `/api/v1/admin/backups/{id}` | Get backup detail (checksum, path) |
| GET | `/api/v1/admin/dr-drills` | List DR drill records |
| POST | `/api/v1/admin/dr-drills` | Record DR drill result |

### 7.22 Admin Settings
| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/admin/settings` | List system settings |
| PATCH | `/api/v1/admin/settings` | Update system settings |
| GET | `/api/v1/admin/audit-log` | Query audit log (admin) |

---

## 8. Concrete Payload Contracts

Every request body and response `data` section below is normative. Field names, types, and enum values are locked against the migration column definitions and PHP enums under `app/Enums/`. Optional fields are marked with `?`. All monetary values are integers in cents (`*_cents`). All timestamps are ISO-8601 with timezone.

### 8.1 Auth / Session

**POST `/api/v1/auth/login`**
- Request: `{ "email": string, "password": string }`
- Response 200: `{ "data": { "token": string, "expires_at": iso8601, "user": UserResource } }`
- Errors: 401 `UNAUTHENTICATED`, 423 `ACCOUNT_LOCKED`, 422 `VALIDATION_FAILED`.

**POST `/api/v1/auth/logout`**
- Request: empty body; requires valid bearer token.
- Response 200: `{ "data": { "logged_out": true } }` (current token revoked).

**GET `/api/v1/auth/me`**
- Response 200: `{ "data": UserResource }` with roles array.

### 8.2 Users

`UserResource`: `{ "id": int, "name": string, "email": string, "status": "active"|"locked"|"disabled", "locale": string, "last_login_at"?: iso8601 }`.

User and role management CRUD endpoints are not implemented in the current scope (see questions.md §1). Role assignment is done via the seeder or direct DB tooling. The `GET /api/v1/auth/me` endpoint returns the authenticated user's profile with their current role grants.

### 8.3 Terms, Courses, Sections, Enrollments

`TermResource`: `{ "id": int, "name": string, "starts_on": date, "ends_on": date, "status": "upcoming"|"active"|"archived" }`.

`CourseResource`: `{ "id": int, "code": string, "title": string, "description"?: string, "status": "active"|"archived" }`.

`SectionResource`: `{ "id": int, "course_id": int, "term_id": int, "section_code": string, "capacity": int, "status": "active"|"archived" }`.

`EnrollmentResource`: `{ "id": int, "user_id": int, "section_id": int, "status": "enrolled"|"withdrawn"|"completed", "enrolled_at": iso8601, "withdrawn_at"?: iso8601 }`.

### 8.4 Roster Import

**POST `/api/v1/terms/{termId}/roster-imports`** (multipart)
- Request: `file` (CSV, UTF-8), columns `email,name,section_code`.
- Response 202: `{ "data": { "import_id": int, "status": "pending" } }`.
- Errors: 403 `FORBIDDEN` (registrar scope required), 422 `VALIDATION_FAILED`.

`RosterImportResource`: `{ "id": int, "term_id": int, "source_filename": string, "row_count": int, "success_count": int, "error_count": int, "status": "pending"|"running"|"completed"|"failed", "completed_at"?: iso8601 }`.

### 8.5 Grade Items

`GradeItemResource`: `{ "id": int, "section_id": int, "title": string, "max_score": int, "weight_bps": int, "state": "draft"|"published", "published_at"?: iso8601 }`.

**POST `/api/v1/sections/{sectionId}/grade-items/{itemId}/publish`**
- Request: `{ "scores": [{ "user_id": int, "score": int }] }`.
- Response 200: `{ "data": GradeItemResource }` — transitions `state` to `published`, enqueues notification fan-out (`type = grade.published`, category `announcements`).
- Errors: 409 `CONFLICT` if already published.

### 8.6 Threads, Posts, Comments

`ThreadResource`: `{ "id": int, "course_id": int, "section_id"?: int, "author_id": int, "thread_type": "announcement"|"discussion", "qa_enabled": bool, "title": string, "body": string, "state": "visible"|"hidden"|"locked", "created_at": iso8601, "edited_at"?: iso8601 }`.

`PostResource`: `{ "id": int, "thread_id": int, "author_id": int, "parent_post_id"?: int, "body": string, "state": "visible"|"hidden", "created_at": iso8601, "edited_at"?: iso8601 }`.

`CommentResource`: `{ "id": int, "post_id": int, "author_id": int, "body": string, "state": "visible"|"hidden", "created_at": iso8601, "edited_at"?: iso8601 }`.

**POST `/api/v1/threads`**
- Request: `{ "section_id": int, "type": "discussion"|"announcement", "title": string, "body": string }`.
- Response 201: `{ "data": ThreadResource }`.
- Errors: 422 `SENSITIVE_WORDS_BLOCKED` with `blocked_terms` list (rendered by `ApiExceptionRenderer` from `SensitiveWordMatched`).

**PATCH `/api/v1/threads/{threadId}/posts/{id}`** — author within 15 min.
- Request: `{ "body": string }`.
- Response 200: `{ "data": PostResource }`.
- Errors: 423 `EDIT_WINDOW_EXPIRED`, 422 `SENSITIVE_WORDS_BLOCKED`.

**POST `/api/v1/posts/{postId}/reports`**
- Request: `{ "reason": string, "notes"?: string }`.
- Response 201: `{ "data": { "id": int, "status": "open" } }`.

### 8.7 Moderation

**POST `/api/v1/admin/threads/{id}/hide|restore|lock`**
- Request: `{ "reason"?: string }`.
- Response 200: `{ "data": ThreadResource }` with updated `state`.
- Errors: 422 `INVALID_STATE_TRANSITION` (e.g., hiding a locked thread).

**GET `/api/v1/admin/moderation/queue`**
- Response 200: paginated `{ "data": { "data": [ThreadResource], ... } }`.

### 8.8 Sensitive-Word Rules

**POST `/api/v1/admin/sensitive-words`**
- Request: `{ "pattern": string, "match_type": "exact"|"substring", "is_active": bool }`.
- Response 201: `{ "data": { "id": int, "pattern": string, "match_type": string, "is_active": bool } }`.

### 8.9 Notifications

`NotificationResource`: `{ "id": int, "category": "announcements"|"mentions"|"billing"|"system", "type": string, "title": string, "body": string, "payload": object, "read_at"?: iso8601, "created_at": iso8601 }`.

**GET `/api/v1/notifications/unread-count`**
- Response 200: `{ "data": { "total": int, "by_category": { "announcements": int, "mentions": int, "billing": int, "system": int } } }`.

**POST `/api/v1/notifications/mark-read`**
- Request: `{ "ids"?: [int], "category"?: NotificationCategory }`.
- Response 200: same shape as unread-count (updated).

**PATCH `/api/v1/notifications/preferences`**
- Request: `{ "subscriptions": [{ "category": NotificationCategory, "enabled": bool }] }`.
- Response 200: `{ "data": { "subscriptions": [...] } }`.

### 8.10 Orders, Payments, Receipts

`OrderResource`: `{ "id": int, "user_id": int, "status": OrderStatus, "subtotal_cents": int, "tax_cents": int, "total_cents": int, "auto_close_at": iso8601, "paid_at"?: iso8601, "canceled_at"?: iso8601, "redeemed_at"?: iso8601, "lines": [OrderLineResource] }`.

`OrderLineResource`: `{ "id": int, "catalog_item_id": int, "quantity": int, "unit_price_cents": int, "tax_rule_snapshot": object, "line_total_cents": int }`.

**POST `/api/v1/orders`**
- Request: `{ "lines": [{ "catalog_item_id": int, "quantity": int }] }`.
- Response 201: `{ "data": OrderResource }`.

**POST `/api/v1/orders/{orderId}/payment/complete`** — `idempotent`.
- Request: `{ "method": "cash"|"check"|"local_terminal"|"waiver", "amount_cents": int, "kiosk_id"?: string, "reference"?: string }`.
- Headers: `Idempotency-Key: <uuid-v4>` **required**.
- Response 200: `{ "data": { "order": OrderResource, "receipt": ReceiptResource } }`.
- Replay: same response with `X-Idempotent-Replay: true`.
- Errors: 400 `IDEMPOTENCY_KEY_REQUIRED`, 409 `IDEMPOTENCY_KEY_CONFLICT` (same key, different payload), 409 `INVALID_STATE_TRANSITION` (order not `pending_payment`), 422 `VALIDATION_FAILED` (amount ≠ total).

`ReceiptResource`: `{ "id": int, "order_id": int, "receipt_number": string, "issued_at": iso8601 }`.

### 8.11 Bills, Ledger, Refunds, Reconciliation

`BillResource`: `{ "id": int, "user_id": int, "type": BillType, "subtotal_cents": int, "tax_cents": int, "total_cents": int, "paid_cents": int, "refunded_cents": int, "status": BillStatus, "issued_on": date, "due_on": date, "past_due_at"?: iso8601, "paid_at"?: iso8601, "lines": [BillLineResource] }`.

**POST `/api/v1/admin/bills/generate`** — `idempotent`.
- Request: `{ "as_of": date }` (defaults to today).
- Response 202: `{ "data": { "scheduled_count": int, "generated_bill_ids": [int] } }`.

**POST `/api/v1/bills/{billId}/refunds`** — `idempotent`.
- Request: `{ "amount_cents": int, "reason_code_id": int, "notes"?: string }`.
- Response 201: `{ "data": RefundResource }`.
- Errors: 422 `REFUND_EXCEEDS_BALANCE` (`RefundPolicy::EXCEEDS_REFUNDABLE_BALANCE`), 422 `REASON_CODE_REQUIRED`, 409 `IDEMPOTENCY_KEY_CONFLICT`.

`RefundResource`: `{ "id": int, "bill_id": int, "amount_cents": int, "reason_code_id": int, "status": RefundStatus, "reversal_ledger_entry_id"?: int, "approved_at"?: iso8601, "completed_at"?: iso8601 }`.

`LedgerEntryResource`: `{ "id": int, "user_id": int, "bill_id"?: int, "order_id"?: int, "entry_type": LedgerEntryType, "amount_cents": int (signed), "description": string, "reference_entry_id"?: int, "correlation_id": uuid, "created_at": iso8601 }`.

**GET `/api/v1/admin/reconciliation/summary`**
- Response 200: `{ "data": { "open_flags": int, "resolved_today": int, "unreconciled_cents": int } }`.

### 8.12 Appointments

`AppointmentResource`: `{ "id": int, "owner_user_id": int, "resource_type": "facility"|"registrar_meeting"|"generic", "resource_ref"?: string, "scheduled_start": iso8601, "scheduled_end": iso8601, "status": "scheduled"|"rescheduled"|"canceled"|"completed", "notes"?: string }`.

**PATCH `/api/v1/appointments/{id}`**
- Request: `{ "scheduled_start"?: iso8601, "scheduled_end"?: iso8601, "status"?: AppointmentStatus, "notes"?: string }`.
- Response 200: `{ "data": AppointmentResource }`. On change, enqueues notification (`type = appointment.rescheduled`, category `system`).

### 8.13 Enrollments

**POST `/api/v1/enrollments/{id}/approve`** — staff only.
- Request: empty body (or `{}`).
- Response 200: `{ "data": { "id": int, "user_id": int, "section_id": int, "status": "enrolled", "enrolled_at": iso8601 } }`.
- Errors: 403 `FORBIDDEN` (non-staff), 404 `NOT_FOUND`.
- Side-effect: dispatches `enrollment.approved` notification to the enrolled user via `NotificationOrchestrator`.

**POST `/api/v1/enrollments/{id}/deny`** — staff only.
- Request: empty body (or `{}`).
- Response 200: `{ "data": { "id": int, "user_id": int, "section_id": int, "status": "withdrawn", "withdrawn_at": iso8601 } }`.
- Side-effect: dispatches `enrollment.denied` notification to the user.

### 8.14 Roster Import

**POST `/api/v1/terms/{termId}/roster-imports`** — multipart, staff/registrar-scoped.
- Request: `Content-Type: multipart/form-data`, field `file` (CSV, UTF-8), columns `email,name,section_code`.
- Response 202: `{ "data": { "import_id": int, "status": "pending" } }`.
- Errors: 403 `FORBIDDEN` (non-registrar or wrong term scope), 422 `VALIDATION_FAILED` (wrong MIME).

**GET `/api/v1/terms/{termId}/roster-imports`**
- Response 200: `{ "data": [RosterImportResource], "meta": PaginationMeta }`.

`RosterImportResource`: `{ "id": int, "term_id": int, "source_filename": string, "row_count": int, "success_count": int, "error_count": int, "status": "pending"|"running"|"completed"|"failed", "completed_at"?: iso8601 }`.

**GET `/api/v1/roster-imports/{importId}`**
- Response 200: `{ "data": RosterImportResource & { "errors": [{ "row": int, "field": string, "message": string }] } }`.

### 8.15 Billing Schedules and Tax Rules

`BillingScheduleResource`: `{ "id": int, "user_id": int, "fee_category_id": int, "schedule_type": string, "status": "active"|"closed", "start_on": date, "end_on"?: date, "next_run_on": date, "amount_cents": int }`.

**PATCH `/api/v1/billing-schedules/{id}`**
- Request: `{ "status"?: "closed", "end_on"?: date }`.
- Response 200: `{ "data": BillingScheduleResource }`.

`TaxRuleResource`: `{ "id": int, "fee_category_id": int, "rate_bps": int, "effective_from": date, "effective_to"?: date }`.

**POST `/api/v1/admin/fee-categories/{id}/tax-rules`** — admin only.
- Request: `{ "rate_bps": int (0–100000), "effective_from": date, "effective_to"?: date }`.
- Response 201: `{ "data": TaxRuleResource }`.
- Errors: 422 `VALIDATION_FAILED` (effective_to must be after effective_from).

**PATCH `/api/v1/admin/fee-categories/{id}/tax-rules/{taxRuleId}`**
- Request: `{ "rate_bps"?: int, "effective_to"?: date }`.
- Response 200: `{ "data": TaxRuleResource }`.

### 8.16 Health, Metrics, Diagnostics

**GET `/api/health`** (public)
- Response 200: `{ "data": { "status": "ok"|"degraded", "database": bool, "queue": bool, "scheduler": bool } }`.

**GET `/api/v1/health/circuit`**
- Response 200: `{ "data": { "mode": "read_write"|"read_only", "tripped_at"?: iso8601, "tripped_reason"?: string } }`.

**POST `/api/v1/admin/diagnostics/export`** — `idempotent`.
- Request: `{ "scope"?: "logs"|"metrics"|"full" }`.
- Response 202: `{ "data": { "export_id": int, "status": "pending" } }`.

---

## 9. Canonical Error Catalog

Every error produced by `app/Exceptions/ApiExceptionRenderer` is listed here. The renderer is engaged for requests under `/api/*` or when `Accept: application/json`. Codes are stable identifiers safe to branch on in the UI.

| HTTP | Code | Source | Meaning |
|---|---|---|---|
| 400 | `BAD_REQUEST` | Malformed body/query | Unparseable JSON or obviously-malformed input |
| 400 | `IDEMPOTENCY_KEY_REQUIRED` | `IdempotencyMiddleware` | `Idempotency-Key` header missing on required route |
| 401 | `UNAUTHENTICATED` | `AuthenticationException` | Missing/invalid bearer token |
| 403 | `FORBIDDEN` | `AuthorizationException` | Authenticated but lacks required capability/scope |
| 404 | `NOT_FOUND` | `ModelNotFoundException`, `NotFoundHttpException` | Resource does not exist |
| 405 | `METHOD_NOT_ALLOWED` | `MethodNotAllowedHttpException` | HTTP verb not supported on route |
| 409 | `IDEMPOTENCY_KEY_CONFLICT` | `IdempotencyReplay` | Same key with different canonical payload |
| 409 | `INVALID_STATE_TRANSITION` | `InvalidStateTransition` | Domain state machine rejected the event |
| 422 | `VALIDATION_FAILED` | `ValidationException` | Form-request validation failed |
| 422 | `SENSITIVE_WORDS_BLOCKED` | `SensitiveWordMatched` | Body contains blocked terms; payload carries ranges |
| 422 | `REFUND_EXCEEDS_BALANCE` | `RefundExceedsBalance` | Requested refund > refundable balance |
| 423 | `EDIT_WINDOW_EXPIRED` | `EditWindowExpired` | Self-edit attempted past 15-minute window |
| 429 | `RATE_LIMITED` | `TooManyRequestsHttpException` | Too many requests |
| 503 | `SERVICE_UNAVAILABLE` | Circuit breaker `ReadOnly` | Mutation refused while breaker is tripped |
| 500 | `INTERNAL_ERROR` | Unhandled throwable | Catch-all; correlation id is always present |

Every error envelope is emitted as:
```json
{ "error": { "code": "<STABLE_CODE>", "message": "<string>", "details"?: object } }
```
`X-Correlation-Id` is attached to every response (generated by `CorrelationIdMiddleware` if absent from the request).

---

## 10. Idempotency Middleware Contract

The `idempotent` middleware alias is registered in `bootstrap/app.php` and points at `app/Http/Middleware/IdempotencyMiddleware`. Applying it to a route creates the following contract:

1. Clients must send a non-empty `Idempotency-Key` header. A UUID v4 is recommended.
2. The middleware derives `scope` from the route name (falls back to `method path` if unnamed) and a `key_hash = sha256(key)`.
3. A `request_fingerprint = sha256(canonical_json(body))` is computed. Replays with the same key but different fingerprint raise 409 `IDEMPOTENCY_KEY_CONFLICT`.
4. On first execution, the downstream handler runs and its `(status, body)` pair is stored in `idempotency_keys` with `expires_at = created_at + 24h`.
5. On replay, the cached `(status, body)` is returned verbatim with `X-Idempotent-Replay: true`. First-play responses always carry `X-Idempotent-Replay: false`.
6. Entries older than `expires_at` are pruned by a scheduler pass (deferred to Prompt 3+). Uniqueness `(scope, key_hash)` guarantees race-safety.

Routes that require this middleware in Prompt 2+:
- `POST /api/v1/orders/{orderId}/payment` and `/payment/complete`
- `POST /api/v1/admin/bills/generate`
- `POST /api/v1/bills/{billId}/refunds`
- `POST /api/v1/admin/diagnostics/export`
- `POST /api/v1/admin/backups/trigger`
- `POST /api/v1/_contract/echo` (test harness only; exercises the middleware end-to-end)
