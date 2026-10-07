<x-layouts::app :title="'Manage Reviews'">
    @include('commerce.messages')

    @php
        $field = 'rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800';
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Manage Reviews
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <form method="GET"
          action="{{ route('admin.reviews.index') }}"
          class="my-5 flex flex-wrap gap-3">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search review, car or member"
            aria-label="Search review, car or member"
            class="{{ $field }}"
        >

        <select name="rating" aria-label="Rating" class="{{ $field }}">
            <option value="">All ratings</option>

            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>
                    {{ $i }} {{ Str::plural('star', $i) }}
                </option>
            @endfor
        </select>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white">
            Filter
        </button>

        <a href="{{ route('admin.reviews.index') }}"
           class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-600">
            Reset
        </a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Car</th>
                    <th class="p-3">Member</th>
                    <th class="p-3">Rating</th>
                    <th class="p-3">Review</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($reviews as $review)
                    <tr class="border-t border-zinc-200 align-top dark:border-zinc-700">
                        <td class="p-3">
                            #{{ $review->review_id }}
                        </td>

                        <td class="p-3">
                            <a href="{{ route('products.show', $review->car_id) }}#reviews"
                               class="text-blue-600">
                                {{ $review->car?->brand?->brand_name }}
                                {{ $review->car?->model_name }}
                            </a>
                        </td>

                        <td class="p-3">
                            {{ $review->member?->name }}
                            <br>
                            <span class="text-zinc-500">{{ $review->member?->email }}</span>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            @include('reviews._stars', ['rating' => $review->rating])
                        </td>

                        <td class="max-w-md p-3">
                            <p class="whitespace-pre-wrap">{{ $review->comment }}</p>
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            {{ $review->created_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="p-3">
                            <form method="POST"
                                  action="{{ route('admin.reviews.destroy', $review) }}"
                                  data-confirm="Delete review #{{ $review->review_id }}?"
                                  onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="text-red-600">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            class="p-6 text-center text-zinc-500">
                            No reviews found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $reviews->links() }}
    </div>
</x-layouts::app>
