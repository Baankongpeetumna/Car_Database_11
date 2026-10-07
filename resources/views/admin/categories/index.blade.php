<x-layouts::app :title="'Manage Categories'">
    @include('commerce.messages')

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Manage Categories
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <div class="my-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET"
              action="{{ route('admin.categories.index') }}"
              class="flex flex-wrap gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search category"
                aria-label="Search category"
                class="rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800"
            >

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white">
                Search
            </button>

            <a href="{{ route('admin.categories.index') }}"
               class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-600">
                Reset
            </a>
        </form>

        <a href="{{ route('admin.categories.create') }}"
           class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
            + Add Category
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Category Name</th>
                    <th class="p-3">Car Models</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">
                            #{{ $category->category_id }}
                        </td>

                        <td class="p-3">
                            {{ $category->category_name }}
                        </td>

                        <td class="p-3">
                            {{ number_format($category->cars_count) }}
                        </td>

                        <td class="p-3">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="text-blue-600">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.categories.destroy', $category) }}"
                                      data-confirm="Delete category {{ $category->category_name }}?"
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-red-600">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"
                            class="p-6 text-center text-zinc-500">
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>
</x-layouts::app>
