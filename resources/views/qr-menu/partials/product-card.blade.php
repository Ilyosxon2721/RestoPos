<div
    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex cursor-pointer hover:shadow-md transition-shadow"
    @click="openProduct({
        name: '{{ addslashes($product->name) }}',
        description: {{ $product->description ? "'" . addslashes($product->description) . "'" : 'null' }},
        image: {{ $product->image ? "'" . $product->image . "'" : 'null' }},
        price: '{{ $product->price }}',
        weight: {{ $product->weight ? "'" . $product->weight . "'" : 'null' }},
        cooking_time: {{ $product->cooking_time ?: 'null' }},
        calories: {{ $product->calories ?: 'null' }},
        proteins: {{ $product->proteins ? "'" . $product->proteins . "'" : 'null' }},
        fats: {{ $product->fats ? "'" . $product->fats . "'" : 'null' }},
        carbs: {{ $product->carbohydrates ? "'" . $product->carbohydrates . "'" : 'null' }}
    })"
>
    @if($qrMenu->show_images && $product->image)
        <div class="w-24 h-24 flex-shrink-0 bg-gray-100">
            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
        </div>
    @endif

    <div class="flex-1 p-3 flex flex-col justify-between min-w-0">
        <div>
            <h4 class="font-semibold text-gray-900 text-sm leading-tight truncate">{{ $product->name }}</h4>
            @if($qrMenu->show_descriptions && $product->description)
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $product->description }}</p>
            @endif
        </div>
        <div class="flex items-center justify-between mt-2">
            <span class="text-sm font-bold text-primary">
                {{ number_format((float)$product->price, 0, '.', ' ') }} {{ $qrMenu->currency }}
            </span>
            @if($product->weight)
                <span class="text-xs text-gray-400">{{ $product->weight }} г</span>
            @endif
        </div>
    </div>
</div>
