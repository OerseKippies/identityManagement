-- Phase 7 copM integration probe seed (read-only consumption tests)

INSERT INTO idm_service_accounts (serviceAccountId, accountName, description, status, createdAt, updatedAt)
SELECT '00000000-0000-4000-8000-000000000001', 'copm-service', 'copM Phase 7 commL consumer probe', 'ACTIVE', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (
    SELECT 1 FROM idm_service_accounts WHERE serviceAccountId = '00000000-0000-4000-8000-000000000001'
);

INSERT INTO idm_users (userId, username, displayName, email, status, createdAt, updatedAt)
SELECT 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'copm.probe', 'copM Probe User', 'copm.probe@example.test', 'ACTIVE', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (
    SELECT 1 FROM idm_users WHERE userId = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'
);
