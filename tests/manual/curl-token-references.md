# Manual Curl: Token References

Create:

```bash
curl -X POST http://127.0.0.1:8080/token-references -H "Content-Type: application/json" -d "{\"subjectType\":\"USER\",\"subjectId\":\"{userId}\",\"expiresAt\":\"2026-12-31 23:59:59\"}"
```

List:

```bash
curl http://127.0.0.1:8080/token-references
```

Get:

```bash
curl http://127.0.0.1:8080/token-references/{tokenReferenceId}
```

Revoke:

```bash
curl -X POST http://127.0.0.1:8080/token-references/{tokenReferenceId}/revoke
```
