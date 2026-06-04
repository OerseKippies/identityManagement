CREATE TABLE IF NOT EXISTS idm_users (
  userId CHAR(36) NOT NULL,
  username VARCHAR(120) NOT NULL,
  displayName VARCHAR(180) NOT NULL,
  email VARCHAR(255) NOT NULL,
  status VARCHAR(24) NOT NULL,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  PRIMARY KEY (userId),
  UNIQUE KEY uq_idm_users_username (username),
  UNIQUE KEY uq_idm_users_email (email),
  KEY idx_idm_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_roles (
  roleId CHAR(36) NOT NULL,
  roleCode VARCHAR(120) NOT NULL,
  roleName VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status VARCHAR(24) NOT NULL,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  PRIMARY KEY (roleId),
  UNIQUE KEY uq_idm_roles_roleCode (roleCode),
  KEY idx_idm_roles_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_permissions (
  permissionId CHAR(36) NOT NULL,
  permissionCode VARCHAR(160) NOT NULL,
  permissionName VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status VARCHAR(24) NOT NULL,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  PRIMARY KEY (permissionId),
  UNIQUE KEY uq_idm_permissions_permissionCode (permissionCode),
  KEY idx_idm_permissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_user_roles (
  userRoleId CHAR(36) NOT NULL,
  userId CHAR(36) NOT NULL,
  roleId CHAR(36) NOT NULL,
  assignedAt DATETIME NOT NULL,
  assignedByType VARCHAR(24) NOT NULL,
  assignedById CHAR(36) NULL,
  PRIMARY KEY (userRoleId),
  UNIQUE KEY uq_idm_user_roles_user_role (userId, roleId),
  KEY idx_idm_user_roles_userId (userId),
  KEY idx_idm_user_roles_roleId (roleId),
  CONSTRAINT fk_idm_user_roles_user FOREIGN KEY (userId) REFERENCES idm_users (userId),
  CONSTRAINT fk_idm_user_roles_role FOREIGN KEY (roleId) REFERENCES idm_roles (roleId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_role_permissions (
  rolePermissionId CHAR(36) NOT NULL,
  roleId CHAR(36) NOT NULL,
  permissionId CHAR(36) NOT NULL,
  assignedAt DATETIME NOT NULL,
  assignedByType VARCHAR(24) NOT NULL,
  assignedById CHAR(36) NULL,
  PRIMARY KEY (rolePermissionId),
  UNIQUE KEY uq_idm_role_permissions_role_permission (roleId, permissionId),
  KEY idx_idm_role_permissions_roleId (roleId),
  KEY idx_idm_role_permissions_permissionId (permissionId),
  CONSTRAINT fk_idm_role_permissions_role FOREIGN KEY (roleId) REFERENCES idm_roles (roleId),
  CONSTRAINT fk_idm_role_permissions_permission FOREIGN KEY (permissionId) REFERENCES idm_permissions (permissionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_service_accounts (
  serviceAccountId CHAR(36) NOT NULL,
  accountName VARCHAR(160) NOT NULL,
  description TEXT NULL,
  status VARCHAR(24) NOT NULL,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  PRIMARY KEY (serviceAccountId),
  UNIQUE KEY uq_idm_service_accounts_accountName (accountName),
  KEY idx_idm_service_accounts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_service_account_roles (
  serviceAccountRoleId CHAR(36) NOT NULL,
  serviceAccountId CHAR(36) NOT NULL,
  roleId CHAR(36) NOT NULL,
  assignedAt DATETIME NOT NULL,
  assignedByType VARCHAR(24) NOT NULL,
  assignedById CHAR(36) NULL,
  PRIMARY KEY (serviceAccountRoleId),
  UNIQUE KEY uq_idm_service_account_roles_account_role (serviceAccountId, roleId),
  KEY idx_idm_service_account_roles_serviceAccountId (serviceAccountId),
  KEY idx_idm_service_account_roles_roleId (roleId),
  CONSTRAINT fk_idm_service_account_roles_account FOREIGN KEY (serviceAccountId) REFERENCES idm_service_accounts (serviceAccountId),
  CONSTRAINT fk_idm_service_account_roles_role FOREIGN KEY (roleId) REFERENCES idm_roles (roleId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_access_policies (
  policyId CHAR(36) NOT NULL,
  policyCode VARCHAR(160) NOT NULL,
  policyName VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status VARCHAR(24) NOT NULL,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  PRIMARY KEY (policyId),
  UNIQUE KEY uq_idm_access_policies_policyCode (policyCode),
  KEY idx_idm_access_policies_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_token_references (
  tokenReferenceId CHAR(36) NOT NULL,
  subjectType VARCHAR(32) NOT NULL,
  subjectId CHAR(36) NOT NULL,
  issuedAt DATETIME NOT NULL,
  expiresAt DATETIME NOT NULL,
  revokedAt DATETIME NULL,
  status VARCHAR(24) NOT NULL,
  PRIMARY KEY (tokenReferenceId),
  KEY idx_idm_token_references_subject (subjectType, subjectId),
  KEY idx_idm_token_references_status (status),
  KEY idx_idm_token_references_expiresAt (expiresAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_audit_log (
  auditId CHAR(36) NOT NULL,
  entityType VARCHAR(64) NOT NULL,
  entityId CHAR(36) NOT NULL,
  action VARCHAR(48) NOT NULL,
  actorType VARCHAR(32) NOT NULL,
  actorId CHAR(36) NULL,
  timestamp DATETIME NOT NULL,
  detailsJson JSON NULL,
  PRIMARY KEY (auditId),
  KEY idx_idm_audit_log_entity (entityType, entityId),
  KEY idx_idm_audit_log_action (action),
  KEY idx_idm_audit_log_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS idm_schema_migrations (
  migration VARCHAR(180) NOT NULL,
  appliedAt DATETIME NOT NULL,
  PRIMARY KEY (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
