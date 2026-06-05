# Health Endpoint Evidence

Date: 2026-06-05
Server command:

```text
D:\Programs\PHP\php.exe -S localhost:8080 -t public/api
```

Request:

```http
GET /v1/health HTTP/1.1
Host: localhost:8080
X-Correlation-Id: a1b2c3d4-e5f6-4789-a012-3456789abcde
```

Result: PASS

HTTP status:

```text
200
```

Response headers:

```text
Content-Type: application/json
X-Correlation-Id: a1b2c3d4-e5f6-4789-a012-3456789abcde
X-Api-Version: v1
```

Response body:

```json
{
  "status": "healthy",
  "module": "identityManagement",
  "moduleCode": "idM",
  "version": "v1",
  "timestamp": "2026-06-05T18:54:59Z"
}
```

Assessment:

Health endpoint returns expected JSON payload. Correlation header is echoed. No API key required for health route.
