# Code Review

Review the specified file or directory for issues:

Target: $ARGUMENTS

## Check for:

### 1. Code Quality
- [ ] PSR-12 compliance
- [ ] Proper type hints (PHP 8.3 features)
- [ ] No unused imports/variables
- [ ] Proper naming conventions
- [ ] Single responsibility principle

### 2. Security
- [ ] SQL injection vulnerabilities
- [ ] XSS vulnerabilities
- [ ] Mass assignment protection
- [ ] Proper authorization checks
- [ ] Input validation
- [ ] Sensitive data exposure

### 3. Performance
- [ ] N+1 query problems
- [ ] Missing indexes in queries
- [ ] Unnecessary loops
- [ ] Memory leaks
- [ ] Cache opportunities

### 4. Laravel Best Practices
- [ ] Use of Eloquent properly
- [ ] Proper use of service container
- [ ] Middleware placement
- [ ] Route model binding
- [ ] FormRequest usage

### 5. Testing
- [ ] Tests exist for the code
- [ ] Test coverage is adequate
- [ ] Edge cases are tested

## Output Format:

For each issue found:
1. **Location**: File and line number
2. **Severity**: Critical / Warning / Info
3. **Issue**: Description of the problem
4. **Fix**: Suggested solution with code example

## Example:

```
📍 app/Services/OrderService.php:45
⚠️ Warning: N+1 Query Problem

Issue: Loading customer for each order in loop
Current:
foreach ($orders as $order) {
    $customer = $order->customer;
}

Fix: Use eager loading
$orders = Order::with('customer')->get();
```
