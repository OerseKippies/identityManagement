# idM Dependency Graph

Status: Architecture Foundation

## Allowed Flow

```text
Caller Module
  -> communicationLayer (commL)
  -> identityManagement (idM)
```

```text
identityManagement (idM)
  -> communicationLayer (commL)
  -> Caller Module
```

## Governance Dependencies

```text
idM -> OK-Core governance documents
idM -> communicationLayer contracts and routing
idM -> idM-owned MariaDB schema
```

## Forbidden Flow

```text
idM -> Foreign Module Database
idM -> Foreign Module Internal Code
Foreign Module -> idM Database
Foreign Module -> idM Internal Code
Module -> Module direct
```
