<x-layouts::app :title="'Edit Brand'">
    @include('commerce.messages')

    <a href="{{ route('admin.brands.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← All Brands
    </a>

    <h1 class="mb-4 text-2xl font-semibold">
        Edit Brand #{{ $brand->brand_id }}
    </h1>

    <form method="POST"
          action="{{ route('admin.brands.update', $brand) }}"
          class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        @csrf
        @method('PUT')

        @include('admin.brands._form')

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2 text-white">
            Save Changes
        </button>
    </form>
</x-layouts::app>
