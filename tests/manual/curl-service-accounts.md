# Manual Curl: Service Accounts

Create:

```bash
curl -X POST http://127.0.0.1:8080/service-accounts -H "Content-Type: application/json" -d "{\"accountName\":\"idm-sync\"}"
```

List:

```bash
curl http://127.0.0.1:8080/service-accounts
```

Get:

```bash
curl http://127.0.0.1:8080/service-accounts/{serviceAccountId}
```

Update:

```bash
curl -X PATCH http://127.0.0.1:8080/service-accounts/{serviceAccountId} -H "Content-Type: application/json" -d "{\"description\":\"MVP service account\"}"
```

State changes:

```bash
curl -X POST http://127.0.0.1:8080/service-accounts/{serviceAccountId}/enable
curl -X POST http://127.0.0.1:8080/service-accounts/{serviceAccountId}/disable
curl -X POST http://127.0.0.1:8080/service-accounts/{serviceAccountId}/lock
curl -X POST http://127.0.0.1:8080/service-accounts/{serviceAccountId}/unlock
```
