# Manual Curl: Roles

Create:

```bash
curl -X POST http://127.0.0.1:8080/roles -H "Content-Type: application/json" -d "{\"roleCode\":\"administrator\",\"roleName\":\"Administrator\"}"
```

List:

```bash
curl http://127.0.0.1:8080/roles
```

Get:

```bash
curl http://127.0.0.1:8080/roles/{roleId}
```

Update:

```bash
curl -X PATCH http://127.0.0.1:8080/roles/{roleId} -H "Content-Type: application/json" -d "{\"roleName\":\"Admin\"}"
```

Disable:

```bash
curl -X POST http://127.0.0.1:8080/roles/{roleId}/disable
```

Assign/remove user role:

```bash
curl -X POST http://127.0.0.1:8080/users/{userId}/roles/{roleId}
curl -X DELETE http://127.0.0.1:8080/users/{userId}/roles/{roleId}
```
