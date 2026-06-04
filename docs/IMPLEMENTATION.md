# idM Implementation

Status: MVP Implementation Foundation Complete

## Runtime

The implementation is dependency-free PHP 8.3 using PDO for MariaDB.

No Composer package manager decision is required for this MVP foundation.

## Entry Point

```text
public/index.php
```

The entry point loads:

```text
config/config.php
```

If that file does not exist, it falls back to:

```text
config/config.example.php
```

## Layers

```text
src/Http
src/Database
src/Repository
src/Domain
src/Support
```

## Boundary

The implementation creates only idM-owned access identity entities:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

It does not implement canonical identity, identity mapping, foreign object identifiers, or cross-domain business-object identity.
