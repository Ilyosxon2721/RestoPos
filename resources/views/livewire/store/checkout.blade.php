<div class="max-w-2xl mx-auto px-4 py-6 pb-24 md:pb-8" x-data="checkoutPage()">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Оформление заказа</h1>

    @if($error)
        <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">{{ $error }}</div>
    @endif

    {{-- Тип заказа --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
        <h3 class="font-bold text-gray-900 mb-3">Тип заказа</h3>
        <div class="grid grid-cols-2 gap-2">
            @if($store->delivery_enabled)
                <button
                    wire:click="$set('orderType', 'delivery')"
                    class="flex items-center gap-2 p-3 rounded-xl border-2 transition {{ $orderType === 'delivery' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5 {{ $orderType === 'delivery' ? 'text-primary' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    <div class="text-left">
                        <p class="text-sm font-medium {{ $orderType === 'delivery' ? 'text-primary' : 'text-gray-900' }}">Доставка</p>
                    </div>
                </button>
            @endif
            @if($store->pickup_enabled)
                <button
                    wire:click="$set('orderType', 'pickup')"
                    class="flex items-center gap-2 p-3 rounded-xl border-2 transition {{ $orderType === 'pickup' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5 {{ $orderType === 'pickup' ? 'text-primary' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <div class="text-left">
                        <p class="text-sm font-medium {{ $orderType === 'pickup' ? 'text-primary' : 'text-gray-900' }}">Самовывоз</p>
                    </div>
                </button>
            @endif
        </div>
    </div>

    {{-- Адрес доставки --}}
    @if($orderType === 'delivery')
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
            <h3 class="font-bold text-gray-900 mb-3">Адрес доставки</h3>

            {{-- Сохранённые адреса --}}
            @if($this->addresses->count() > 0)
                <div class="space-y-2 mb-3">
                    @foreach($this->addresses as $address)
                        <label class="flex items-start gap-3 p-3 rounded-lg cursor-pointer transition {{ $selectedAddressId === $address->id ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50 hover:bg-gray-100' }}">
                            <input
                                type="radio"
                                wire:model.live="selectedAddressId"
                                value="{{ $address->id }}"
                                class="mt-0.5 text-primary focus:ring-primary"
                            >
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $address->label }}</span>
                                <p class="text-sm text-gray-600">{{ $address->full_address }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- Новый адрес --}}
            <button
                wire:click="$toggle('showNewAddress')"
                class="text-sm text-primary font-medium hover:opacity-80"
            >
                {{ $showNewAddress ? 'Скрыть' : '+ Новый адрес' }}
            </button>

            @if($showNewAddress)
                <div class="mt-3 space-y-3">
                    {{-- Яндекс Карта --}}
                    <div
                        id="checkout-map"
                        class="w-full h-48 rounded-lg bg-gray-100 border border-gray-200"
                        x-data="checkoutMap()"
                        x-init="initMap()"
                    ></div>
                    <input
                        type="text"
                        wire:model="newAddress"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary"
                        placeholder="Улица, дом"
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" wire:model="newApartment" class="px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="Квартира">
                        <input type="text" wire:model="newEntrance" class="px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="Подъезд">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" wire:model="newFloor" class="px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="Этаж">
                        <input type="text" wire:model="newIntercom" class="px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="Домофон">
                    </div>
                </div>
            @endif

            {{-- Информация о доставке --}}
            @if($deliveryInfo)
                <div class="mt-3 p-3 bg-blue-50 text-blue-700 text-sm rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $deliveryInfo }}
                </div>
            @endif
        </div>
    @endif

    {{-- Самовывоз — выбор филиала --}}
    @if($orderType === 'pickup')
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
            <h3 class="font-bold text-gray-900 mb-3">Точка самовывоза</h3>
            <div class="space-y-2">
                @foreach($this->branches as $branch)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-gray-900 text-sm">{{ $branch->name }}</p>
                        @if($branch->address)
                            <p class="text-sm text-gray-600">{{ $branch->address }}</p>
                        @endif
                        @if($branch->phone)
                            <p class="text-sm text-gray-500 mt-1">{{ $branch->phone }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Контактные данные --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
        <h3 class="font-bold text-gray-900 mb-3">Контактные данные</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Имя</label>
                <input type="text" wire:model="contactName" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary">
                @error('contactName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Телефон</label>
                <input type="tel" wire:model="contactPhone" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary">
                @error('contactPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Комментарий к заказу</label>
                <textarea wire:model="comment" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary" placeholder="Пожелания, аллергии..."></textarea>
            </div>
        </div>
    </div>

    {{-- Способ оплаты --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
        <h3 class="font-bold text-gray-900 mb-3">Способ оплаты</h3>
        <div class="space-y-2">
            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition {{ $paymentMethod === 'cash' ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50 hover:bg-gray-100' }}">
                <input type="radio" wire:model.live="paymentMethod" value="cash" class="text-primary focus:ring-primary">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="text-sm font-medium text-gray-900">Наличные</span>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition {{ $paymentMethod === 'card' ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50 hover:bg-gray-100' }}">
                <input type="radio" wire:model.live="paymentMethod" value="card" class="text-primary focus:ring-primary">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span class="text-sm font-medium text-gray-900">Картой при получении</span>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition {{ $paymentMethod === 'online' ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50 hover:bg-gray-100' }}">
                <input type="radio" wire:model.live="paymentMethod" value="online" class="text-primary focus:ring-primary">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="text-sm font-medium text-gray-900">Онлайн (Payme, Click, Uzum)</span>
            </label>
        </div>
    </div>

    {{-- Итого --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-4">
        <h3 class="font-bold text-gray-900 mb-3">Ваш заказ</h3>
        <div class="space-y-2 mb-3">
            <template x-for="item in cart" :key="item.id">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700"><span x-text="item.name"></span> x<span x-text="item.qty"></span></span>
                    <span class="font-medium text-gray-900" x-text="formatPrice(item.price * item.qty)"></span>
                </div>
            </template>
        </div>
        <div class="border-t border-gray-100 pt-3 space-y-1.5">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Подытог:</span>
                <span class="font-medium" x-text="formatPrice(cartTotal)"></span>
            </div>
            @if($orderType === 'delivery' && $deliveryFee > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Доставка:</span>
                    <span class="font-medium" x-text="formatPrice({{ $deliveryFee }})"></span>
                </div>
            @elseif($orderType === 'delivery' && $deliveryFee == 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Доставка:</span>
                    <span class="font-medium text-green-600">Бесплатно</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-100">
                <span>Итого:</span>
                <span class="text-primary" x-text="formatPrice(cartTotal + {{ $deliveryFee }})"></span>
            </div>
        </div>
    </div>

    {{-- Минимальная сумма заказа --}}
    @if($store->min_order_amount > 0)
        <div class="mb-4 p-3 bg-yellow-50 text-yellow-700 text-sm rounded-lg" x-show="cartTotal < {{ $store->min_order_amount }}">
            Минимальная сумма заказа: <span x-text="formatPrice({{ $store->min_order_amount }})"></span>
        </div>
    @endif

    {{-- Кнопка оформления --}}
    <button
        @click="submitOrder()"
        class="w-full py-3.5 bg-primary text-white font-bold rounded-xl text-lg hover:opacity-90 transition disabled:opacity-50"
        :disabled="cartCount === 0 {{ $store->min_order_amount > 0 ? '|| cartTotal < ' . $store->min_order_amount : '' }}"
        wire:loading.attr="disabled"
    >
        <span wire:loading.remove wire:target="createOrder">Оформить заказ</span>
        <span wire:loading wire:target="createOrder">Оформляем...</span>
    </button>
</div>

<script>
    function checkoutPage() {
        return {
            submitOrder() {
                const cart = JSON.parse(localStorage.getItem('shop_cart') || '[]');
                @this.call('createOrder', cart);
            },
            init() {
                Livewire.on('get-cart-for-checkout', () => {
                    this.submitOrder();
                });
                Livewire.on('clear-cart-after-order', () => {
                    localStorage.setItem('shop_cart', '[]');
                });
            }
        }
    }

    function checkoutMap() {
        return {
            map: null,
            marker: null,
            initMap() {
                if (typeof ymaps === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://api-maps.yandex.ru/2.1/?apikey=&lang=ru_RU';
                    script.onload = () => ymaps.ready(() => this.createMap());
                    document.head.appendChild(script);
                } else {
                    ymaps.ready(() => this.createMap());
                }
            },
            createMap() {
                this.map = new ymaps.Map('checkout-map', {
                    center: [41.2995, 69.2401],
                    zoom: 13,
                    controls: ['zoomControl', 'geolocationControl']
                });
                this.marker = new ymaps.Placemark([41.2995, 69.2401], {}, {
                    draggable: true,
                    preset: 'islands#redDotIcon'
                });
                this.map.geoObjects.add(this.marker);

                this.marker.events.add('dragend', () => {
                    const coords = this.marker.geometry.getCoordinates();
                    @this.set('newLat', coords[0]);
                    @this.set('newLng', coords[1]);
                    this.reverseGeocode(coords);
                });

                this.map.events.add('click', (e) => {
                    const coords = e.get('coords');
                    this.marker.geometry.setCoordinates(coords);
                    @this.set('newLat', coords[0]);
                    @this.set('newLng', coords[1]);
                    this.reverseGeocode(coords);
                });

                // Автоопределение
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        const coords = [pos.coords.latitude, pos.coords.longitude];
                        this.map.setCenter(coords, 15);
                        this.marker.geometry.setCoordinates(coords);
                        @this.set('newLat', coords[0]);
                        @this.set('newLng', coords[1]);
                        this.reverseGeocode(coords);
                    });
                }
            },
            reverseGeocode(coords) {
                ymaps.geocode(coords).then((res) => {
                    const first = res.geoObjects.get(0);
                    if (first) {
                        @this.set('newAddress', first.getAddressLine());
                    }
                });
            }
        }
    }
</script>
