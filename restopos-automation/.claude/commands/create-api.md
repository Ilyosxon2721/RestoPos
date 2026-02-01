# Create API Endpoint

Create a complete REST API endpoint:

Endpoint: $ARGUMENTS

## Tasks:

1. **Controller** (`app/Http/Controllers/Api/`)
   - Use invokable controller or resource controller
   - Return proper JSON responses
   - Use API Resources for transformation
   - Handle exceptions gracefully

2. **Form Request** (`app/Http/Requests/`)
   - Validation rules
   - Authorization logic
   - Custom messages (ru/uz/en)

3. **API Resource** (`app/Http/Resources/`)
   - Transform model to JSON
   - Include relationships conditionally
   - Add meta information

4. **Route** (`routes/api.php`)
   - Use proper HTTP verbs
   - Apply middleware (auth:sanctum, throttle)
   - Group related routes

5. **Tests** (`tests/Feature/Api/`)
   - Test successful response
   - Test validation errors
   - Test authorization
   - Test edge cases

## Response Format:

```json
{
  "success": true,
  "data": { },
  "message": "Success message",
  "meta": {
    "pagination": { }
  }
}
```

## Error Format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error description",
    "details": { }
  }
}
```

Follow RESTful conventions and Laravel best practices.
