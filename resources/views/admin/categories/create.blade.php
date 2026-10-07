<x-layouts::app :title="'Add Category'">
    @include('commerce.messages')

    <a href="{{ route('admin.categories.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← All Categories
    </a>

    <h1 class="mb-4 text-2xl font-semibold">
        Add Category
    </h1>

    <form method="POST"
          action="{{ route('admin.categories.store') }}"
          class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        @csrf

        @include('admin.categories._form')

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2 text-white">
            Save
        </button>
    </form>
</x-layouts::app>
