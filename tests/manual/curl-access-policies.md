# Manual Curl: Access Policies

Create:

```bash
curl -X POST http://127.0.0.1:8080/access-policies -H "Content-Type: application/json" -d "{\"policyCode\":\"default-access\",\"policyName\":\"Default access\"}"
```

List:

```bash
curl http://127.0.0.1:8080/access-policies
```

Get:

```bash
curl http://127.0.0.1:8080/access-policies/{policyId}
```

Update:

```bash
curl -X PATCH http://127.0.0.1:8080/access-policies/{policyId} -H "Content-Type: application/json" -d "{\"policyName\":\"Default access policy\"}"
```

State changes:

```bash
curl -X POST http://127.0.0.1:8080/access-policies/{policyId}/activate
curl -X POST http://127.0.0.1:8080/access-policies/{policyId}/retire
```
