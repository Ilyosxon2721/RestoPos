# Create Domain Module

Create a new domain module with the following structure:

Module name: $ARGUMENTS

## Tasks:

1. Create directory structure:
   ```
   app/Domain/{ModuleName}/
   ├── Actions/
   ├── DTOs/
   ├── Events/
   ├── Exceptions/
   ├── Jobs/
   ├── Models/
   ├── Policies/
   ├── Repositories/
   └── Services/
   ```

2. Create base files:
   - Model with fillable, casts, relationships
   - Migration with proper fields
   - Factory for testing
   - Seeder with sample data
   - Policy with CRUD methods
   - FormRequest for validation
   - Resource for API responses

3. Register in service provider

4. Create basic CRUD Actions:
   - Create{ModuleName}Action
   - Update{ModuleName}Action
   - Delete{ModuleName}Action

5. Create API routes in routes/api.php

6. Create basic tests

Follow Laravel 12 and PHP 8.3 best practices.
Use strict types, readonly DTOs, and proper type hints.
