# idM API Usage

Status: MVP Implementation Foundation Complete

## Base URL

Local PHP server example:

```bash
php -S 127.0.0.1:8080 -t public
```

Base URL:

```text
http://127.0.0.1:8080
```

## Health

```bash
curl http://127.0.0.1:8080/health
```

## JSON Headers

Use:

```text
Content-Type: application/json
X-Correlation-ID: optional-correlation-id
```

## Error Shape

```json
{
  "error": {
    "errorCode": "VALIDATION_ERROR",
    "errorMessage": "Human-readable message",
    "correlationId": "uuid-or-generated-id",
    "timestamp": "ISO-8601"
  }
}
```
