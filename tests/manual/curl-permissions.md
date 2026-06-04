# Manual Curl: Permissions

Create:

```bash
curl -X POST http://127.0.0.1:8080/permissions -H "Content-Type: application/json" -d "{\"permissionCode\":\"user.read\",\"permissionName\":\"Read users\"}"
```

List:

```bash
curl http://127.0.0.1:8080/permissions
```

Get:

```bash
curl http://127.0.0.1:8080/permissions/{permissionId}
```

Assign/remove role permission:

```bash
curl -X POST http://127.0.0.1:8080/roles/{roleId}/permissions/{permissionId}
curl -X DELETE http://127.0.0.1:8080/roles/{roleId}/permissions/{permissionId}
```
