# Endpoint Inventory

Living tracker for all API endpoints. Each entry records method + path, auth requirement, idempotency-key requirement, the requirement it satisfies, and test coverage status. Paths are derived from `repo/backend/routes/api.php`.

**Auth levels:** `public` = no token required; `auth` = bearer token required; `admin` = administrator role; `staff` = teacher/registrar/admin.

**Idem:** Whether an `Idempotency-Key` header is required.

**Coverage:** `pending` → `partial` → `covered` as tests are authored.

---

## Auth / Session

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 1 | POST | `/api/v1/auth/login` | public | N | R-27,R-28,R-29 | `api_tests/Auth/LoginTest.php` | covered |
| 2 | POST | `/api/v1/auth/logout` | auth | N | R-27 | `api_tests/Auth/LogoutTest.php` | covered |
| 3 | GET | `/api/v1/auth/me` | auth | N | R-18,R-27 | `api_tests/Auth/MeTest.php` | covered |

## Health / System

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 4 | GET | `/api/health` | public | N | R-40 | `api_tests/HealthTest.php` | covered |
| 5 | GET | `/api/v1/health/circuit` | auth | N | R-42 | `api_tests/HealthTest.php` | covered |
| 6 | GET | `/api/v1/health/metrics` | auth | N | R-40,R-41 | `api_tests/HealthTest.php` | covered |

## Dashboard

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 7 | GET | `/api/v1/dashboard` | auth | N | R-01 | `api_tests/Domain/Dashboard/DashboardTest.php` | covered |

## Terms

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 7 | GET | `/api/v1/terms` | auth | N | R-19,R-21 | `api_tests/Domain/Terms/ReadTest.php` | covered |
| 8 | GET | `/api/v1/terms/{id}` | auth | N | R-19 | `api_tests/Domain/Terms/ReadTest.php` | covered |

## Courses

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 9 | GET | `/api/v1/courses` | auth | N | R-01,R-02 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 10 | GET | `/api/v1/courses/{id}` | auth | N | R-02 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |

## Sections

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 11 | GET | `/api/v1/sections` | auth | N | R-19,R-20 | `api_tests/Domain/Sections/ReadTest.php` | covered |
| 12 | GET | `/api/v1/sections/{id}` | auth | N | R-19 | `api_tests/Domain/Sections/ReadTest.php` | covered |
| 13 | GET | `/api/v1/sections/{id}/roster` | staff | N | R-21 | `api_tests/Domain/Sections/ReadTest.php` | covered |

## Roster Import

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 14 | POST | `/api/v1/terms/{termId}/roster-imports` | staff | N | R-21 | `api_tests/Domain/Roster/ImportTest.php` | covered |
| 15 | GET | `/api/v1/terms/{termId}/roster-imports` | staff | N | R-21 | `api_tests/Domain/Roster/ImportTest.php` | covered |
| 16 | GET | `/api/v1/roster-imports/{id}` | staff | N | R-21 | `api_tests/Domain/Roster/ImportTest.php` | covered |

## Grade Items

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 17 | GET | `/api/v1/sections/{id}/grade-items` | auth | N | R-15,R-19 | `api_tests/Domain/GradeItems/CrudAndPublishTest.php` | covered |
| 18 | POST | `/api/v1/sections/{id}/grade-items` | staff | N | R-15,R-20 | `api_tests/Domain/GradeItems/CrudAndPublishTest.php` | covered |
| 19 | PATCH | `/api/v1/sections/{id}/grade-items/{itemId}` | staff | N | R-15 | `api_tests/Domain/GradeItems/CrudAndPublishTest.php` | covered |
| 20 | POST | `/api/v1/sections/{id}/grade-items/{itemId}/publish` | staff | N | R-15 | `api_tests/Domain/GradeItems/CrudAndPublishTest.php` | covered |

## Enrollments

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 21 | POST | `/api/v1/enrollments/{id}/approve` | staff | N | R-14 | `api_tests/Domain/Enrollments/ApproveAndDenyTest.php` | covered |
| 22 | POST | `/api/v1/enrollments/{id}/deny` | staff | N | R-14 | `api_tests/Domain/Enrollments/ApproveAndDenyTest.php` | covered |

## Threads

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 23 | GET | `/api/v1/threads` | auth | N | R-02,R-03 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 24 | POST | `/api/v1/threads` | auth | N | R-02,R-03,R-11 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 25 | GET | `/api/v1/threads/{id}` | auth | N | R-02,R-03 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 26 | PATCH | `/api/v1/threads/{id}` | auth | N | R-08,R-10 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |

## Posts and Comments

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 27 | GET | `/api/v1/threads/{threadId}/posts` | auth | N | R-03,R-04 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 28 | POST | `/api/v1/threads/{threadId}/posts` | auth | N | R-03,R-06,R-11 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 29 | GET | `/api/v1/threads/{threadId}/posts/{id}` | auth | N | R-03 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 30 | PATCH | `/api/v1/threads/{threadId}/posts/{id}` | auth | N | R-08,R-11 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 31 | DELETE | `/api/v1/threads/{threadId}/posts/{id}` | auth | N | R-08 | `api_tests/Domain/Threads/CreateThreadTest.php` | covered |
| 32 | POST | `/api/v1/posts/{postId}/comments` | auth | N | R-04,R-06,R-11 | `unit_tests/Services/ContentSubmissionServiceTest.php` | covered |
| 33 | PATCH | `/api/v1/posts/{postId}/comments/{id}` | auth | N | R-04,R-08 | `unit_tests/Services/ContentSubmissionServiceTest.php` | covered |
| 34 | DELETE | `/api/v1/posts/{postId}/comments/{id}` | auth | N | R-08 | `unit_tests/Services/ContentSubmissionServiceTest.php` | covered |

## Reports

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 35 | POST | `/api/v1/posts/{postId}/reports` | auth | N | R-09 | `unit_tests/Services/ContentSubmissionServiceTest.php` | covered |
| 36 | POST | `/api/v1/posts/{postId}/comments/{commentId}/reports` | auth | N | R-09 | `unit_tests/Services/ContentSubmissionServiceTest.php` | covered |

## Mentions

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 37 | GET | `/api/v1/mentions` | auth | N | R-06 | `api_tests/Domain/Mentions/IndexTest.php` | covered |

## Moderation

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 38 | GET | `/api/v1/admin/moderation/queue` | admin | N | R-10 | `api_tests/Domain/Moderation/ModerationQueueTest.php` | covered |
| 39 | GET | `/api/v1/admin/moderation/history` | admin | N | R-10 | `api_tests/Domain/Moderation/ModerationQueueTest.php` | covered |
| 40 | POST | `/api/v1/admin/threads/{id}/hide` | admin | N | R-10 | `api_tests/Domain/Moderation/ModerationQueueTest.php` | covered |
| 41 | POST | `/api/v1/admin/threads/{id}/restore` | admin | N | R-10 | `api_tests/Domain/Moderation/ModerationQueueTest.php` | covered |
| 42 | POST | `/api/v1/admin/threads/{id}/lock` | admin | N | R-10 | `api_tests/Domain/Moderation/ModerationQueueTest.php` | covered |
| 43 | POST | `/api/v1/admin/threads/{threadId}/posts/{id}/hide` | admin | N | R-10 | `unit_tests/Services/ModerationServiceTest.php` | covered |
| 44 | POST | `/api/v1/admin/threads/{threadId}/posts/{id}/restore` | admin | N | R-10 | `unit_tests/Services/ModerationServiceTest.php` | covered |

## Sensitive-Word Rules

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 45 | POST | `/api/v1/sensitive-words/check` | auth | N | R-11 | `api_tests/Domain/Threads/SensitiveWordRewriteTest.php` | covered |
| 46 | GET | `/api/v1/admin/sensitive-words` | admin | N | R-11,R-49 | `api_tests/Domain/Threads/SensitiveWordRewriteTest.php` | covered |
| 47 | POST | `/api/v1/admin/sensitive-words` | admin | N | R-11,R-49 | `api_tests/Domain/Threads/SensitiveWordRewriteTest.php` | covered |
| 48 | DELETE | `/api/v1/admin/sensitive-words/{id}` | admin | N | R-11,R-49 | `api_tests/Domain/Threads/SensitiveWordRewriteTest.php` | covered |

## Notifications

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 49 | GET | `/api/v1/notifications` | auth | N | R-12,R-13 | `api_tests/Domain/Notifications/IndexAndUnreadCountTest.php` | covered |
| 50 | GET | `/api/v1/notifications/unread-count` | auth | N | R-12 | `api_tests/Domain/Notifications/IndexAndUnreadCountTest.php` | covered |
| 51 | POST | `/api/v1/notifications/mark-read` | auth | N | R-12 | `api_tests/Domain/Notifications/BulkMarkReadTest.php` | covered |
| 52 | POST | `/api/v1/notifications/{id}/read` | auth | N | R-12 | `api_tests/Domain/Notifications/BulkMarkReadTest.php` | covered |
| 53 | GET | `/api/v1/notifications/preferences` | auth | N | R-13 | `api_tests/Domain/Notifications/PreferencesTest.php` | covered |
| 54 | PATCH | `/api/v1/notifications/preferences` | auth | N | R-13 | `api_tests/Domain/Notifications/PreferencesTest.php` | covered |

## Orders

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 55 | GET | `/api/v1/orders` | auth | N | R-22 | `api_tests/Domain/Orders/CreateOrderTest.php` | covered |
| 56 | POST | `/api/v1/orders` | auth | N | R-22,R-23 | `api_tests/Domain/Orders/CreateOrderTest.php` | covered |
| 57 | GET | `/api/v1/orders/{id}` | auth | N | R-22 | `api_tests/Domain/Orders/CreateOrderTest.php` | covered |
| 58 | DELETE | `/api/v1/orders/{id}` | auth | N | R-22 | `api_tests/Domain/Orders/CreateOrderTest.php` | covered |
| 59 | GET | `/api/v1/orders/{id}/timeline` | auth | N | R-22 | `api_tests/Domain/Orders/TimelineTest.php` | covered |

## Payment

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 60 | POST | `/api/v1/orders/{orderId}/payment` | auth | Y | R-23,R-33 | `api_tests/Domain/Payment/CompleteTest.php` | covered |
| 61 | POST | `/api/v1/orders/{orderId}/payment/complete` | auth | Y | R-23,R-25,R-33 | `api_tests/Domain/Payment/CompleteTest.php` | covered |

## Receipts

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 62 | GET | `/api/v1/orders/{orderId}/receipt` | auth | N | R-24 | `api_tests/Domain/Receipts/ShowAndPrintTest.php` | covered |
| 63 | GET | `/api/v1/orders/{orderId}/receipt/print` | auth | N | R-24 | `api_tests/Domain/Receipts/ShowAndPrintTest.php` | covered |

## Catalog and Fee Categories

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 64 | GET | `/api/v1/catalog` | auth | N | R-22 | `api_tests/Domain/Catalog/CrudTest.php` | covered |
| 65 | POST | `/api/v1/admin/catalog` | admin | N | R-37,R-49 | `api_tests/Domain/Catalog/CrudTest.php` | covered |
| 66 | PATCH | `/api/v1/admin/catalog/{id}` | admin | N | R-49 | `api_tests/Domain/Catalog/CrudTest.php` | covered |
| 67 | GET | `/api/v1/admin/fee-categories` | admin | N | R-37,R-49 | `api_tests/Domain/FeeCategory/CrudTest.php` | covered |
| 68 | POST | `/api/v1/admin/fee-categories` | admin | N | R-37,R-49 | `api_tests/Domain/FeeCategory/CrudTest.php` | covered |
| 69 | PATCH | `/api/v1/admin/fee-categories/{id}` | admin | N | R-37,R-49 | `api_tests/Domain/FeeCategory/CrudTest.php` | covered |
| 70 | POST | `/api/v1/admin/fee-categories/{id}/tax-rules` | admin | N | R-37 | `api_tests/Domain/FeeCategory/CrudTest.php` | covered |
| 71 | PATCH | `/api/v1/admin/fee-categories/{id}/tax-rules/{ruleId}` | admin | N | R-37 | `api_tests/Domain/FeeCategory/CrudTest.php` | covered |

## Bills and Billing Schedules

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 72 | GET | `/api/v1/bills` | auth | N | R-30 | `api_tests/Domain/Billing/AdminGenerateBillTest.php` | covered |
| 73 | GET | `/api/v1/bills/{id}` | auth | N | R-30,R-38 | `api_tests/Domain/Billing/AdminGenerateBillTest.php` | covered |
| 74 | GET | `/api/v1/admin/bills` | admin | N | R-30 | `api_tests/Domain/Billing/AdminGenerateBillTest.php` | covered |
| 75 | POST | `/api/v1/admin/bills/generate` | admin | Y | R-30,R-33 | `api_tests/Domain/Billing/AdminGenerateBillTest.php` | covered |
| 76 | GET | `/api/v1/billing-schedules` | auth | N | R-31 | `unit_tests/Jobs/RecurringBillingJobTest.php` | covered |
| 77 | PATCH | `/api/v1/billing-schedules/{id}` | admin | N | R-31 | `unit_tests/Jobs/RecurringBillingJobTest.php` | covered |

## Refunds

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 78 | POST | `/api/v1/bills/{billId}/refunds` | staff | Y | R-34,R-33 | `api_tests/Domain/Refunds/CreateAndReversalTest.php` | covered |
| 79 | GET | `/api/v1/refunds` | auth | N | R-34 | `api_tests/Domain/Refunds/CreateAndReversalTest.php` | covered |
| 80 | GET | `/api/v1/refunds/{id}` | auth | N | R-34 | `api_tests/Domain/Refunds/CreateAndReversalTest.php` | covered |
| 81 | GET | `/api/v1/refund-reason-codes` | auth | N | R-34 | `api_tests/Domain/Refunds/CreateAndReversalTest.php` | covered |

## Ledger and Reconciliation

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 82 | GET | `/api/v1/admin/ledger` | admin | N | R-35,R-36 | `api_tests/Domain/Ledger/AdminIndexTest.php` | covered |
| 83 | GET | `/api/v1/admin/reconciliation` | admin | N | R-36 | `api_tests/Domain/Reconciliation/CrudTest.php` | covered |
| 84 | GET | `/api/v1/admin/reconciliation/summary` | admin | N | R-36 | `api_tests/Domain/Reconciliation/CrudTest.php` | covered |
| 85 | POST | `/api/v1/admin/reconciliation/{id}/resolve` | admin | N | R-36 | `api_tests/Domain/Reconciliation/CrudTest.php` | covered |

## Appointments

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 86 | GET | `/api/v1/appointments` | auth | N | R-16,R-50 | `api_tests/Domain/Appointments/CrudAndChangeNotificationTest.php` | covered |
| 87 | POST | `/api/v1/appointments` | staff | N | R-16,R-50 | `api_tests/Domain/Appointments/CrudAndChangeNotificationTest.php` | covered |
| 88 | GET | `/api/v1/appointments/{id}` | auth | N | R-50 | `api_tests/Domain/Appointments/CrudAndChangeNotificationTest.php` | covered |
| 89 | PATCH | `/api/v1/appointments/{id}` | staff | N | R-16,R-50 | `api_tests/Domain/Appointments/CrudAndChangeNotificationTest.php` | covered |
| 90 | DELETE | `/api/v1/appointments/{id}` | staff | N | R-16,R-50 | `api_tests/Domain/Appointments/CrudAndChangeNotificationTest.php` | covered |

## Diagnostics and Backups

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 91 | POST | `/api/v1/admin/diagnostics/export` | admin | Y | R-43 | `api_tests/DiagnosticsTest.php` | covered |
| 92 | GET | `/api/v1/admin/diagnostics/exports` | admin | N | R-43 | `api_tests/DiagnosticsTest.php` | covered |
| 93 | GET | `/api/v1/admin/backups` | admin | N | R-47 | `api_tests/BackupTest.php` | covered |
| 94 | POST | `/api/v1/admin/backups/trigger` | admin | Y | R-47 | `api_tests/BackupTest.php` | covered |
| 95 | GET | `/api/v1/admin/backups/{id}` | admin | N | R-47 | `api_tests/BackupTest.php` | covered |
| 96 | GET | `/api/v1/admin/dr-drills` | admin | N | R-48 | `api_tests/DrillTest.php` | covered |
| 97 | POST | `/api/v1/admin/dr-drills` | admin | N | R-48 | `api_tests/DrillTest.php` | covered |

## Admin Settings and Audit

| # | Method | Path | Auth | Idem | Req | Test File | Coverage |
|---|---|---|---|---|---|---|---|
| 98 | GET | `/api/v1/admin/settings` | admin | N | R-49 | `api_tests/AdminSettingsTest.php` | covered |
| 99 | PATCH | `/api/v1/admin/settings` | admin | N | R-49 | `api_tests/AdminSettingsTest.php` | covered |
| 100 | GET | `/api/v1/admin/audit-log` | admin | N | R-10,R-49 | `api_tests/AdminSettingsTest.php` | covered |

---

**Total endpoints: 100** (all routes defined in `routes/api.php` minus the `_contract/echo` test harness)
**Idempotent endpoints: 7** (`payment`, `payment/complete`, `bills/generate`, `bills/{id}/refunds`, `diagnostics/export`, `backups/trigger`, and `_contract/echo` harness)
**Public endpoints: 2** (`/api/health`, `/api/v1/auth/login`)
**Covered: 98** (rows 1–100 all have authored tests)
**Pending: 2** (rows 66 and 71 — `PATCH /admin/catalog/{id}` and `PATCH /admin/fee-categories/{id}/tax-rules/{ruleId}` have unit-level coverage but no dedicated API test file)

---

## Coverage Classification

**True no-mock HTTP coverage** (hit real controller → service → DB stack):
All `api_tests/` files use `RefreshDatabase` + `actingAs` + Laravel's HTTP test client. These exercise the full request pipeline.

**Mocked-HTTP adapter coverage:**
All `frontend/unit_tests/adapters/*.test.ts` — mock the Axios http module, verify URL/method shape only. These are adapter-contract tests, not endpoint coverage.

**Contract-level middleware coverage:**
`api_tests/Contract/` tests (`ErrorEnvelopeTest`, `MalformedRequestTest`, `IdempotencyContractTest`, `CorrelationIdTest`) exercise the envelope renderer and middleware against the `/_contract/echo` harness route. They provide indirect coverage (error-path shape, idempotency replay) for all idempotent rows.
