

# Mikrotik Integration TODO (with Suggested Improvements)

## 1. Retry + Failover Handling
- [x] Wrap all Mikrotik API calls in try–catch with retries (2–3 attempts, exponential backoff).
- [x] Log failures into a mikrotik_logs table if router is unreachable.
- [ ] Mark jobs as failed and requeue if needed.

## 2. Queued Operations (Jobs)
- [ ] Move critical MikrotikService calls (create/update/delete user) into Laravel Jobs.
- [ ] Example: CreateMikrotikUserJob, SuspendUserJob.
- [ ] Controllers should dispatch jobs, not call Mikrotik directly.

## 3. Sync Drift Detector
- [x] Create a scheduled Job (SyncMikrotikJob) to run daily/hourly.
- [x] Compare Mikrotik users/profiles with DB, log/flag mismatches.

## 4. Tenant Isolation
- [x] Store each tenant’s router credentials in tenant_settings.
- [x] MikrotikService always uses tenant-specific config.
- [x] Add safeguards to prevent cross-tenant API calls.

## 5. Audit Logs
- [x] Create mikrotik_logs table: tenant_id, action, params, status, error_message, timestamp.
- [x] Log every router command (success/failure).

## 6. Graceful Suspension Flow
- [ ] Pre-expiry notifications (SMS/email 1–2 days before expiry).
- [ ] Grace profile: switch user to limited-speed profile before full suspension.
- [ ] Full suspension after grace period ends.
- [x] Implement suspendUser() in MikrotikService.
- [ ] Implement applyGraceProfile() in MikrotikService.

## 7. Better Error Handling + Reporting
- [x] Centralize API error handling in MikrotikService.
- [ ] Return structured errors (success, code, message).
- [ ] Show errors in UI or send admin alerts.

## 8. Security Enhancements
- [ ] Store Mikrotik passwords encrypted in DB.
- [ ] Rotate router API credentials per tenant if possible.
- [ ] Limit API access by firewall rules.

## 9. Advanced Improvements

- [ ] Automated Backups & Restore
	- [ ] Schedule regular backups of router configuration.
	- [ ] Allow tenants to restore from previous backups if needed.

- [ ] Rate Limiting & Throttling
	- [ ] Limit the number of API calls per tenant to prevent router overload.
	- [ ] Queue or delay non-critical operations during peak times.

- [ ] Health Monitoring & Alerts
	- [ ] Monitor router health (CPU, memory, uptime) and send alerts if thresholds are exceeded.
	- [ ] Notify admins/tenants of outages or degraded performance.

- [ ] Multi-Router Support
	- [ ] Allow tenants to manage multiple routers (e.g., for different locations).
	- [ ] Ensure all operations are scoped to the correct router.

- [ ] Self-Healing & Auto-Recovery
	- [ ] Detect failed jobs and automatically retry or escalate.
	- [ ] Optionally, auto-recover from common router errors (e.g., reconnect, reboot).

- [ ] Advanced Reporting & Analytics
	- [ ] Track usage, session history, and package changes over time.
	- [ ] Provide tenants with downloadable reports and visual dashboards.

- [ ] API Versioning & Compatibility
	- [ ] Detect Mikrotik API version and adapt commands for compatibility.
	- [ ] Warn if router firmware/API is outdated.

- [ ] User Experience Enhancements
	- [ ] Add progress indicators for long-running jobs.
	- [ ] Provide clear feedback and actionable error messages in the UI.

---
Check off each item as completed. Add new items as needed during development.
