# Create Livewire Component

Create a Livewire component with full functionality:

Component: $ARGUMENTS

## Requirements:

1. Create Livewire component class in `app/Livewire/`
   - Use PHP 8.3 features (typed properties, readonly where appropriate)
   - Add proper validation with `#[Validate]` attributes
   - Use `#[Computed]` for computed properties
   - Implement proper lifecycle hooks
   - Add pagination if listing data
   - Handle form submission with proper validation

2. Create Blade view in `resources/views/livewire/`
   - Use Tailwind CSS for styling
   - Add Alpine.js for client-side interactions
   - Make it responsive
   - Add loading states with `wire:loading`
   - Add confirmation dialogs for destructive actions

3. Add route in `routes/web.php` if needed

4. Create test in `tests/Feature/Livewire/`
   - Test component rendering
   - Test form validation
   - Test successful submission
   - Test authorization

## Example structure:

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

final class {ComponentName} extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Computed]
    public function items(): Collection
    {
        return Item::query()->get();
    }

    public function save(): void
    {
        $this->validate();
        // logic
    }

    public function render(): View
    {
        return view('livewire.{component-name}');
    }
}
```
