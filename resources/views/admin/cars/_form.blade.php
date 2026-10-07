{{-- ช่องกรอกที่ใช้ร่วมกันระหว่างหน้าเพิ่มและแก้ไข --}}
@php
    $field = 'w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800';
    $label = 'mb-1 block font-semibold';
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="model_name" class="{{ $label }}">Model Name</label>
        <input id="model_name" name="model_name" type="text" required maxlength="255"
               value="{{ old('model_name', $car->model_name) }}" class="{{ $field }}">
    </div>

    <div>
        <label for="brand_id" class="{{ $label }}">Brand</label>
        <select id="brand_id" name="brand_id" required class="{{ $field }}">
            <option value="">Select brand</option>

            @foreach ($brands as $brand)
                <option value="{{ $brand->brand_id }}"
                        @selected((string) old('brand_id', $car->brand_id) === (string) $brand->brand_id)>
                    {{ $brand->brand_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="category_id" class="{{ $label }}">Category</label>
        <select id="category_id" name="category_id" required class="{{ $field }}">
            <option value="">Select category</option>

            @foreach ($categories as $category)
                <option value="{{ $category->category_id }}"
                        @selected((string) old('category_id', $car->category_id) === (string) $category->category_id)>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="model_year" class="{{ $label }}">Model Year</label>
        <input id="model_year" name="model_year" type="number" required
               min="1900" max="{{ now()->year + 1 }}" step="1"
               value="{{ old('model_year', $car->model_year) }}" class="{{ $field }}">
    </div>

    <div>
        <label for="color" class="{{ $label }}">Color</label>
        <input id="color" name="color" type="text" required maxlength="100"
               value="{{ old('color', $car->color) }}" class="{{ $field }}">
    </div>

    <div>
        <label for="fuel_type" class="{{ $label }}">Fuel Type</label>
        <select id="fuel_type" name="fuel_type" required class="{{ $field }}">
            <option value="">Select fuel type</option>

            @foreach ($fuelTypes as $option)
                <option value="{{ $option }}" @selected(old('fuel_type', $car->fuel_type) === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="transmission" class="{{ $label }}">Transmission</label>
        <select id="transmission" name="transmission" required class="{{ $field }}">
            <option value="">Select transmission</option>

            @foreach ($transmissions as $option)
                <option value="{{ $option }}" @selected(old('transmission', $car->transmission) === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="car_condition" class="{{ $label }}">Condition</label>
        <select id="car_condition" name="car_condition" required class="{{ $field }}">
            <option value="">Select condition</option>

            @foreach ($conditions as $option)
                <option value="{{ $option }}" @selected(old('car_condition', $car->car_condition) === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="engine_cc" class="{{ $label }}">Engine (cc)</label>
        <input id="engine_cc" name="engine_cc" type="number" required min="0" max="20000" step="1"
               value="{{ old('engine_cc', $car->engine_cc) }}" class="{{ $field }}">
        <p class="mt-1 text-xs text-zinc-500">Use 0 for electric cars.</p>
    </div>

    <div>
        <label for="mileage_km" class="{{ $label }}">Mileage (km)</label>
        <input id="mileage_km" name="mileage_km" type="number" required min="0" max="2000000" step="1"
               value="{{ old('mileage_km', $car->mileage_km ?? 0) }}" class="{{ $field }}">
    </div>

    <div>
        <label for="price" class="{{ $label }}">Price (฿)</label>
        <input id="price" name="price" type="number" required min="0.01" step="0.01"
               value="{{ old('price', $car->price) }}" class="{{ $field }}">
    </div>

    <div>
        <label for="stock_qty" class="{{ $label }}">Stock</label>
        <input id="stock_qty" name="stock_qty" type="number" required min="0" max="100000" step="1"
               value="{{ old('stock_qty', $car->stock_qty ?? 0) }}" class="{{ $field }}">
        <p class="mt-1 text-xs text-zinc-500">Set to 0 to stop selling this car.</p>
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="{{ $label }}">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="5000"
                  class="{{ $field }}">{{ old('description', $car->description) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label for="image" class="{{ $label }}">Image</label>

        @if ($car->image_url)
            <img src="{{ $car->image_url }}"
                 alt="{{ $car->model_name }}"
                 class="mb-2 h-32 w-56 rounded-lg object-cover">

            <label class="mb-2 flex items-center gap-2 text-sm">
                <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                Remove current image
            </label>
        @endif

        <input id="image" name="image" type="file"
               accept=".jpg,.jpeg,.png,.webp"
               class="{{ $field }}">
        <p class="mt-1 text-xs text-zinc-500">
            JPG, PNG or WEBP, up to 2 MB.
            @if ($car->image_url)
                Uploading a new image replaces the current one.
            @endif
        </p>
    </div>
</div>
