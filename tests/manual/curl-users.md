# Manual Curl: Users

Base URL:

```text
http://127.0.0.1:8080
```

Create:

```bash
curl -X POST http://127.0.0.1:8080/users -H "Content-Type: application/json" -d "{\"username\":\"admin\",\"displayName\":\"Administrator\",\"email\":\"admin@example.test\"}"
```

List:

```bash
curl http://127.0.0.1:8080/users
```

Get:

```bash
curl http://127.0.0.1:8080/users/{userId}
```

Update:

```bash
curl -X PATCH http://127.0.0.1:8080/users/{userId} -H "Content-Type: application/json" -d "{\"displayName\":\"Admin User\"}"
```

State changes:

```bash
curl -X POST http://127.0.0.1:8080/users/{userId}/enable
curl -X POST http://127.0.0.1:8080/users/{userId}/disable
curl -X POST http://127.0.0.1:8080/users/{userId}/lock
curl -X POST http://127.0.0.1:8080/users/{userId}/unlock
```
