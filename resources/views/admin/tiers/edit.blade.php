<x-layouts::app :title="'Edit Tier'">
    @include('commerce.messages')

    <a href="{{ route('admin.tiers.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← All Tiers
    </a>

    <h1 class="mb-4 text-2xl font-semibold">
        Edit Membership Tier #{{ $tier->tier_id }}
    </h1>

    <form method="POST"
          action="{{ route('admin.tiers.update', $tier) }}"
          class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        @csrf
        @method('PUT')

        @include('admin.tiers._form')

        <p class="text-sm text-zinc-500">
            Changing minimum points does not move existing members right away.
            Their tier is recalculated on their next completed order.
        </p>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2 text-white">
            Save Changes
        </button>
    </form>
</x-layouts::app>
