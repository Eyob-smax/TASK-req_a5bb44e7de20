# Disaster Recovery — Restore Runbook

This document describes the procedure to restore a CampusLearn LAN Portal instance from a nightly encrypted backup.

---

## Prerequisites

- Access to the backup storage directory (default: `storage/app/backups/` inside the container).
- The `BACKUP_ENCRYPTION_KEY` environment variable (32-byte hex, 64 characters) that was active when the backup was created.
- A clean deployment environment with the same PHP/Laravel/MySQL stack as the source.

---

## Step 1 — Identify the target backup

List available backup records via the API (admin only) or directly in the storage directory:

```bash
# Via API
curl -H "Authorization: Bearer <token>" http://localhost:8000/api/v1/admin/backups

# Or list files directly
ls -lh storage/app/backups/
```

Select the backup file by date. Verify the `checksum_sha256` recorded in the database matches the file.

---

## Step 2 — Decrypt the backup archive

Use the bundled `EncryptionHelper` CLI wrapper or any AES-256-GCM compatible tool:

```bash
# Inside the backend container
php artisan campuslearn:backup:decrypt \
    --input=storage/app/backups/backup_<id>_<date>.enc \
    --output=/tmp/restore_<date>.sql \
    --key="${BACKUP_ENCRYPTION_KEY}"
```

Verify the decrypted file is valid SQL:

```bash
head -5 /tmp/restore_<date>.sql
# Expected first line: -- MySQL dump ...  or  SET SQL_MODE = ...
```

---

## Step 3 — Restore the database

**WARNING: This will overwrite the current database. Ensure all users are offline first.**

```bash
# Drop and recreate the target database
mysql -u root -p -e "DROP DATABASE campuslearn; CREATE DATABASE campuslearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import the decrypted SQL dump
mysql -u root -p campuslearn < /tmp/restore_<date>.sql
```

---

## Step 4 — Verify data integrity

```bash
# Re-run Laravel migrations (no-op if schema already matches)
php artisan migrate --force

# Check row counts for key tables
php artisan tinker --execute="echo User::count() . ' users, ' . Order::count() . ' orders';"
```

---

## Step 5 — Restart services and test

```bash
# Inside docker compose
docker compose restart backend

# Verify health endpoint
curl http://localhost:8000/api/health
# Expected: {"status":"ok", ...}

# Verify dashboard (with admin credentials)
curl -H "Authorization: Bearer <token>" http://localhost:8000/api/v1/dashboard
```

---

## Step 6 — Record the restoration in DR drill log

If this is a drill, record the outcome via:

```bash
curl -X POST http://localhost:8000/api/v1/admin/dr-drills \
    -H "Authorization: Bearer <token>" \
    -H "Content-Type: application/json" \
    -d '{"drill_date":"<YYYY-MM-DD>","outcome":"passed","notes":"Restore runbook step 6"}'
```

---

## Retention Policy

Nightly backups are retained for 30 days. Backups older than 30 days are marked `pruned` in the `backup_jobs` table and their files are deleted by the scheduler.

---

## Quarterly DR Drill Procedure

Every quarter, the operations team must:

1. Select a backup from the prior week.
2. Restore it to a staging environment following Steps 1–5 above.
3. Verify the application functions end-to-end (login, dashboard, orders, billing).
4. Record the drill result in the DR drill log (Step 6) with `outcome: passed | failed | partial`.
5. Archive the drill record in `docs/dr-drill-records/YYYY-QN.md` with findings and action items.

---

## Contact and Escalation

For unresolved restore failures, contact the campus IT infrastructure team. Do not store credentials in this document.
