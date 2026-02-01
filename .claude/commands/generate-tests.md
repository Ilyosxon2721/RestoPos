# Generate Tests

Generate comprehensive tests for the specified class or feature:

Target: $ARGUMENTS

## Test Types to Create:

### 1. Unit Tests (`tests/Unit/`)
- Test individual methods
- Mock dependencies
- Test edge cases
- Test exceptions

### 2. Feature Tests (`tests/Feature/`)
- Test HTTP endpoints
- Test full request/response cycle
- Test database changes
- Test events/jobs dispatching

## Test Structure:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class {ClassName}Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup code
    }

    /** @test */
    public function it_does_something(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/endpoint', ['data' => 'value']);
        
        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('table', ['column' => 'value']);
    }
}
```

## Required Test Cases:

### For Controllers/Endpoints:
- [ ] Successful response (200/201)
- [ ] Validation errors (422)
- [ ] Unauthorized access (401)
- [ ] Forbidden access (403)
- [ ] Not found (404)
- [ ] Server error handling (500)

### For Services/Actions:
- [ ] Happy path
- [ ] Invalid input
- [ ] Edge cases (empty, null, max values)
- [ ] Exception handling
- [ ] Side effects (events, jobs, notifications)

### For Models:
- [ ] Factory works correctly
- [ ] Relationships work
- [ ] Scopes return correct data
- [ ] Accessors/mutators work
- [ ] Casts work correctly

## Naming Convention:
`it_{action}_{condition}` or `test_{feature}_{expected_result}`

Examples:
- `it_creates_order_with_valid_data`
- `it_fails_to_create_order_without_items`
- `it_calculates_total_with_discounts`
