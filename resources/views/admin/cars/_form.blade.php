{{-- ช่องกรอกที่ใช้ร่วมกันระหว่างหน้าเพิ่มและแก้ไข --}}
@php
    $isEdit = (bool) ($car->exists ?? false);

    $field = 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900
              placeholder:text-zinc-400 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500
              dark:border-zinc-700 dark:bg-zinc-800 dark:text-white';
    $label = 'mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300';
    $hint  = 'mt-1.5 text-xs text-zinc-500';
    $error = 'mt-1.5 text-xs font-medium text-red-600 dark:text-red-400';
    $card  = 'rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900';
    $title = 'mb-4 text-lg font-semibold text-zinc-900 dark:text-white';

    $imageSrc = $car->image_url ?? null;
@endphp

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">

    {{-- ================= LEFT: form sections ================= --}}
    <div class="space-y-6">

        {{-- Main info --}}
        <section class="{{ $card }}">
            <h2 class="{{ $title }}">Main info</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="model_name" class="{{ $label }}">Model name <span class="text-red-500">*</span></label>
                    <input id="model_name" name="model_name" type="text" required maxlength="255"
                           value="{{ old('model_name', $car->model_name) }}" class="{{ $field }}">
                    @error('model_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="brand_id" class="{{ $label }}">Brand <span class="text-red-500">*</span></label>
                    <select id="brand_id" name="brand_id" required class="{{ $field }}">
                        <option value="">Select brand</option>

                        @foreach ($brands as $brand)
                            <option value="{{ $brand->brand_id }}"
                                    @selected((string) old('brand_id', $car->brand_id) === (string) $brand->brand_id)>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="{{ $label }}">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required class="{{ $field }}">
                        <option value="">Select category</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->category_id }}"
                                    @selected((string) old('category_id', $car->category_id) === (string) $category->category_id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="model_year" class="{{ $label }}">Model year <span class="text-red-500">*</span></label>
                    <input id="model_year" name="model_year" type="number" required
                           min="1900" max="{{ now()->year + 1 }}" step="1"
                           value="{{ old('model_year', $car->model_year) }}" class="{{ $field }}">
                    @error('model_year') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="color" class="{{ $label }}">Color <span class="text-red-500">*</span></label>
                    <input id="color" name="color" type="text" required maxlength="100"
                           value="{{ old('color', $car->color) }}" class="{{ $field }}">
                    @error('color') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Specs --}}
        <section class="{{ $card }}">
            <h2 class="{{ $title }}">Specs</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="fuel_type" class="{{ $label }}">Fuel type <span class="text-red-500">*</span></label>
                    <select id="fuel_type" name="fuel_type" required class="{{ $field }}">
                        <option value="">Select fuel type</option>

                        @foreach ($fuelTypes as $option)
                            <option value="{{ $option }}" @selected(old('fuel_type', $car->fuel_type) === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('fuel_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="transmission" class="{{ $label }}">Transmission <span class="text-red-500">*</span></label>
                    <select id="transmission" name="transmission" required class="{{ $field }}">
                        <option value="">Select transmission</option>

                        @foreach ($transmissions as $option)
                            <option value="{{ $option }}" @selected(old('transmission', $car->transmission) === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('transmission') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="car_condition" class="{{ $label }}">Condition <span class="text-red-500">*</span></label>
                    <select id="car_condition" name="car_condition" required class="{{ $field }}">
                        <option value="">Select condition</option>

                        @foreach ($conditions as $option)
                            <option value="{{ $option }}" @selected(old('car_condition', $car->car_condition) === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('car_condition') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="engine_cc" class="{{ $label }}">Engine <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="engine_cc" name="engine_cc" type="number" required min="0" max="20000" step="1"
                               value="{{ old('engine_cc', $car->engine_cc) }}" class="{{ $field }} pr-12">
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-zinc-400">cc</span>
                    </div>
                    <p class="{{ $hint }}">Use 0 for electric cars.</p>
                    @error('engine_cc') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="mileage_km" class="{{ $label }}">Mileage <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="mileage_km" name="mileage_km" type="number" required min="0" max="2000000" step="1"
                               value="{{ old('mileage_km', $car->mileage_km ?? 0) }}" class="{{ $field }} pr-12">
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-zinc-400">km</span>
                    </div>
                    @error('mileage_km') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Sales --}}
        <section class="{{ $card }}">
            <h2 class="{{ $title }}">Price &amp; stock</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="price" class="{{ $label }}">Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-medium text-zinc-500">฿</span>
                        <input id="price" name="price" type="number" required min="0.01" step="0.01"
                               value="{{ old('price', $car->price) }}" class="{{ $field }} pl-8">
                    </div>
                    @error('price') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="stock_qty" class="{{ $label }}">Stock <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="stock_qty" name="stock_qty" type="number" required min="0" max="100000" step="1"
                               value="{{ old('stock_qty', $car->stock_qty ?? 0) }}" class="{{ $field }} pr-14">
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-zinc-400">units</span>
                    </div>
                    <p class="{{ $hint }}">Set to 0 to stop selling this car.</p>
                    @error('stock_qty') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Description --}}
        <section class="{{ $card }}">
            <h2 class="{{ $title }}">Description</h2>

            <label for="description" class="sr-only">Description</label>
            <textarea id="description" name="description" rows="6" maxlength="5000"
                      placeholder="Highlights, condition, equipment…"
                      class="{{ $field }}">{{ old('description', $car->description) }}</textarea>
            @error('description') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </section>
    </div>

    {{-- ================= RIGHT: image + summary ================= --}}
    <aside class="space-y-6 lg:sticky lg:top-4">

        {{-- Image --}}
        <section class="{{ $card }}"
                 x-data="{ preview: null, name: '' }">
            <h2 class="{{ $title }}">Image</h2>

            <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg
                        border border-dashed border-zinc-300 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                <template x-if="preview">
                    <img :src="preview" alt="New image preview" class="h-full w-full object-cover">
                </template>

                <template x-if="! preview">
                    <div class="h-full w-full">
                        @if ($imageSrc)
                            <img src="{{ $imageSrc }}" alt="{{ $car->model_name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-400">
                                No image
                            </div>
                        @endif
                    </div>
                </template>
            </div>

            <p class="mt-2 text-xs text-zinc-500" x-show="preview" x-cloak>
                New image: <span x-text="name" class="font-medium"></span>
            </p>

            <label for="image" class="{{ $label }} mt-4">
                {{ $imageSrc ? 'Replace image' : 'Upload image' }}
            </label>
            <input id="image" name="image" type="file"
                   accept=".jpg,.jpeg,.png,.webp"
                   x-on:change="
                       const f = $event.target.files[0];
                       preview = f ? URL.createObjectURL(f) : null;
                       name = f ? f.name : '';
                   "
                   class="{{ $field }} file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5
                          file:text-xs file:font-semibold file:text-white
                          dark:file:bg-zinc-100 dark:file:text-zinc-900">
            <p class="{{ $hint }}">
                JPG, PNG or WEBP, up to 2 MB.
                @if ($imageSrc)
                    Uploading a new image replaces the current one.
                @endif
            </p>
            @error('image') <p class="{{ $error }}">{{ $message }}</p> @enderror

            @if ($imageSrc)
                <label class="mt-4 flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="remove_image" value="1"
                           class="rounded border-zinc-300 text-red-600 focus:ring-red-500"
                           @checked(old('remove_image'))>
                    Remove current image
                </label>
            @endif
        </section>

        {{-- Summary (edit only) --}}
        @if ($isEdit)
            <section class="{{ $card }}">
                <h2 class="{{ $title }}">Summary</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Car ID</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">#{{ $car->car_id }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Price</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">฿{{ number_format($car->price, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">In stock now</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ number_format($car->stock_qty) }}</dd>
                    </div>
                </dl>
            </section>
        @endif

        <p class="px-1 text-xs text-zinc-500">
            Fields marked <span class="text-red-500">*</span> are required.
        </p>
    </aside>
</div>

{{-- ================= Sticky action bar ================= --}}
<div class="sticky bottom-0 z-10 -mx-2 mt-8 flex items-center justify-between gap-3 border-t border-zinc-200
            bg-white/90 px-4 py-3 backdrop-blur sm:-mx-4
            dark:border-zinc-800 dark:bg-zinc-950/90">
    <p class="hidden text-xs text-zinc-500 sm:block">
        {{ $isEdit ? 'Changes are not saved until you press Save.' : 'The car is added when you press Save.' }}
    </p>

    <div class="ml-auto flex items-center gap-3">
        <a href="{{ route('admin.cars.index') }}"
           class="rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-medium text-zinc-700
                  transition hover:bg-zinc-50
                  dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
            Cancel
        </a>

        <button type="submit"
                class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white
                       shadow-sm transition hover:bg-red-700">
            {{ $isEdit ? 'Save changes' : 'Add car' }}
        </button>
    </div>
</div>