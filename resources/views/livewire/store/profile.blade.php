<div class="max-w-2xl mx-auto px-4 py-6 pb-24 md:pb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Профиль</h1>

    {{-- Карточка клиента --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm mb-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center">
                <span class="text-xl font-bold text-primary">{{ mb_substr(auth('customer')->user()->first_name ?: '?', 0, 1) }}</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ auth('customer')->user()->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ auth('customer')->user()->phone }}</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-lg font-bold text-gray-900">{{ auth('customer')->user()->total_orders }}</p>
                <p class="text-xs text-gray-500">Заказов</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-lg font-bold text-gray-900" x-text="formatPrice({{ auth('customer')->user()->total_spent }})"></p>
                <p class="text-xs text-gray-500">Потрачено</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-lg font-bold text-primary" x-text="formatPrice({{ auth('customer')->user()->bonus_balance }})"></p>
                <p class="text-xs text-gray-500">Бонусы</p>
            </div>
        </div>
    </div>

    {{-- Быстрые ссылки --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('shop.orders', ['slug' => $store->slug]) }}" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="font-medium text-gray-900 text-sm">Мои заказы</span>
        </a>
        <a href="{{ route('shop.favorites', ['slug' => $store->slug]) }}" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <span class="font-medium text-gray-900 text-sm">Избранное</span>
        </a>
    </div>

    {{-- Персональные данные --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Персональные данные</h3>

        @if($saved)
            <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-lg">Данные сохранены</div>
        @endif

        <form wire:submit="saveProfile" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Имя</label>
                    <input type="text" wire:model="firstName" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary focus:border-transparent">
                    @error('firstName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Фамилия</label>
                    <input type="text" wire:model="lastName" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary focus:border-transparent" placeholder="email@example.com">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Дата рождения</label>
                <input type="date" wire:model="birthDate" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary focus:border-transparent">
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary text-white font-medium rounded-lg text-sm hover:opacity-90 transition">
                Сохранить
            </button>
        </form>
    </div>

    {{-- Адреса --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Мои адреса</h3>
            <button wire:click="addAddress" class="text-sm text-primary font-medium hover:opacity-80">+ Добавить</button>
        </div>

        <div class="space-y-2">
            @forelse($this->addresses as $address)
                <div class="flex items-start gap-3 p-3 rounded-lg {{ $address->is_default ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $address->label }}</span>
                            @if($address->is_default)
                                <span class="text-xs text-primary font-medium">По умолчанию</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ $address->full_address }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @unless($address->is_default)
                            <button wire:click="setDefaultAddress({{ $address->id }})" class="p-1.5 text-gray-400 hover:text-primary transition" title="Сделать основным">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        @endunless
                        <button wire:click="editAddress({{ $address->id }})" class="p-1.5 text-gray-400 hover:text-blue-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:click="deleteAddress({{ $address->id }})" wire:confirm="Удалить адрес?" class="p-1.5 text-gray-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">Нет сохранённых адресов</p>
            @endforelse
        </div>
    </div>

    {{-- Выход --}}
    <button wire:click="logout" class="w-full py-3 text-red-600 font-medium rounded-xl border border-red-200 hover:bg-red-50 transition">
        Выйти из аккаунта
    </button>

    {{-- Модальное окно адреса --}}
    @if($showAddressModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end md:items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-black/50" wire:click="$set('showAddressModal', false)"></div>
                <div class="relative bg-white rounded-t-2xl md:rounded-2xl shadow-xl w-full max-w-lg p-6 z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        {{ $editingAddressId ? 'Редактировать адрес' : 'Новый адрес' }}
                    </h3>

                    <form wire:submit="saveAddress" class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Название</label>
                            <select wire:model="addressLabel" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                                <option value="Дом">Дом</option>
                                <option value="Работа">Работа</option>
                                <option value="Другой">Другой</option>
                            </select>
                        </div>

                        {{-- Карта Яндекс --}}
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Адрес на карте</label>
                            <div
                                id="address-map"
                                class="w-full h-48 rounded-lg bg-gray-100 border border-gray-200"
                                x-data="addressMap()"
                                x-init="initMap()"
                            ></div>
                            <input
                                type="text"
                                wire:model="addressText"
                                class="w-full mt-2 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 ring-primary focus:border-transparent"
                                placeholder="Улица, дом"
                                id="address-input"
                            >
                            @error('addressText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Квартира</label>
                                <input type="text" wire:model="apartment" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="12">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Подъезд</label>
                                <input type="text" wire:model="entrance" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="1">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Этаж</label>
                                <input type="text" wire:model="floor" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="3">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Домофон</label>
                                <input type="text" wire:model="intercom" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="12#">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Комментарий</label>
                            <textarea wire:model="addressComment" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" placeholder="Ориентир, особые указания..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="$set('showAddressModal', false)" class="flex-1 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm hover:bg-gray-200 transition">
                                Отмена
                            </button>
                            <button type="submit" class="flex-1 py-2.5 bg-primary text-white font-medium rounded-lg text-sm hover:opacity-90 transition">
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function addressMap() {
                return {
                    map: null,
                    marker: null,
                    initMap() {
                        if (typeof ymaps === 'undefined') {
                            // Подгружаем Яндекс Карты если ещё не загружены
                            const script = document.createElement('script');
                            script.src = 'https://api-maps.yandex.ru/2.1/?apikey=&lang=ru_RU';
                            script.onload = () => ymaps.ready(() => this.createMap());
                            document.head.appendChild(script);
                        } else {
                            ymaps.ready(() => this.createMap());
                        }
                    },
                    createMap() {
                        const lat = @json($latitude) || 41.2995;
                        const lng = @json($longitude) || 69.2401;

                        this.map = new ymaps.Map('address-map', {
                            center: [lat, lng],
                            zoom: 15,
                            controls: ['zoomControl', 'geolocationControl']
                        });

                        this.marker = new ymaps.Placemark([lat, lng], {}, {
                            draggable: true,
                            preset: 'islands#redDotIcon'
                        });

                        this.map.geoObjects.add(this.marker);

                        // Перетаскивание маркера
                        this.marker.events.add('dragend', () => {
                            const coords = this.marker.geometry.getCoordinates();
                            @this.set('latitude', coords[0]);
                            @this.set('longitude', coords[1]);
                            this.reverseGeocode(coords);
                        });

                        // Клик на карту
                        this.map.events.add('click', (e) => {
                            const coords = e.get('coords');
                            this.marker.geometry.setCoordinates(coords);
                            @this.set('latitude', coords[0]);
                            @this.set('longitude', coords[1]);
                            this.reverseGeocode(coords);
                        });

                        // Автоопределение геопозиции
                        if (navigator.geolocation && !@json($latitude)) {
                            navigator.geolocation.getCurrentPosition((pos) => {
                                const coords = [pos.coords.latitude, pos.coords.longitude];
                                this.map.setCenter(coords, 16);
                                this.marker.geometry.setCoordinates(coords);
                                @this.set('latitude', coords[0]);
                                @this.set('longitude', coords[1]);
                                this.reverseGeocode(coords);
                            });
                        }
                    },
                    reverseGeocode(coords) {
                        ymaps.geocode(coords).then((res) => {
                            const firstGeoObject = res.geoObjects.get(0);
                            if (firstGeoObject) {
                                const address = firstGeoObject.getAddressLine();
                                @this.set('addressText', address);
                            }
                        });
                    }
                }
            }
        </script>
    @endif
</div>
