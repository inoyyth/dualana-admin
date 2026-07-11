# API Key Authentication

Protect WordPress REST API using an API Key.

## Header

```
X-API-Key: your-secret-key
```

## Docker

```yaml
environment:
  WP_API_KEY: your-secret-key
```

## Example

```bash
curl \
-H "X-API-Key: your-secret-key" \
http://localhost:8080/wp-json/wp/v2/posts
```
