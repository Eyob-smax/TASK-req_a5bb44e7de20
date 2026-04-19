# audit_report-1 Fix Check (Static Re-Inspection)

Date: 2026-04-19  
Mode: Static-only (no runtime execution, no Docker, no tests run)

## Summary
- Total prior issues checked: 6
- Fixed: 6
- Partially Fixed: 0
- Not Fixed: 0

---

## Issue-by-Issue Verification

### 1) Missing authorization on admin bills index
- Previous finding: High
- Status: **Fixed**
- Current evidence:
  - Authorization present in controller: repo/backend/app/Http/Controllers/Api/BillController.php:39
  - Policy method exists: repo/backend/app/Policies/BillPolicy.php:22
  - API coverage added for admin/non-admin access to /api/v1/admin/bills: repo/backend/api_tests/Domain/Billing/AdminGenerateBillTest.php:60-71
- Verdict: The originally reported gap is remediated.

### 2) Discussion visibility effectively global for authenticated users
- Previous finding: High
- Status: **Fixed**
- Current evidence:
  - ThreadController now enforces role-aware filtering in list endpoint (student enrollment filter; teacher scoped filtering via teacherScopeIds): repo/backend/app/Http/Controllers/Api/ThreadController.php:24-57
  - ThreadPolicy view now enforces scoped access for thread detail (teacher course scope or enrolled student): repo/backend/app/Policies/ThreadPolicy.php:35-61
  - Scope tests now cover student filtering and section/course-scoped teacher access constraints: repo/backend/api_tests/Domain/Threads/ThreadScopeTest.php:15-187
- Verdict: The previously global discussion visibility finding is resolved.

### 3) Health endpoint status code not reflecting queue failure
- Previous finding: Medium
- Status: **Fixed**
- Current evidence:
  - Response status now keyed to overall (db + queue): repo/backend/app/Http/Controllers/Api/HealthController.php:26-35
- Verdict: The inconsistency is corrected.

### 4) Idempotency scoping diverged from endpoint+resource contract
- Previous finding: Medium
- Status: **Fixed**
- Current evidence:
  - Scope now includes route parameters/resource identity when available: repo/backend/app/Http/Middleware/IdempotencyMiddleware.php:69-87
- Verdict: Scoped keying now aligns with the endpoint/resource contract.

### 5) Auth token TTL documentation mismatch
- Previous finding: Medium
- Status: **Fixed**
- Current evidence:
  - API spec now documents CL_TOKEN_TTL_MINUTES default 720 and clarifies SANCTUM_TOKEN_EXPIRY is not used: docs/api-spec.md:67
  - Config wiring remains consistent with this: repo/backend/config/campuslearn.php:49
- Verdict: Documentation and implementation are aligned.

### 6) Backup trigger response nondeterministic job identity
- Previous finding: Low
- Status: **Fixed**
- Current evidence:
  - trigger() now creates and returns a concrete pending backup_jobs row before dispatch: repo/backend/app/Services/BackupService.php:24-40
  - Dispatch includes explicit job ID: repo/backend/app/Services/BackupService.php:36
- Verdict: Deterministic job identity is returned to clients.

---

## Final Conclusion
- All previously reported issues in audit_report-1.md are now fixed based on static re-inspection evidence.
