<x-layouts::app :title="'Manage Membership Tiers'">
    @include('commerce.messages')

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Manage Membership Tiers
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <div class="my-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-zinc-500">
            Members move up a tier when a completed order brings their points to the tier's minimum
            (1 point per ฿1,000). Tiers are sorted by minimum points.
        </p>

        <a href="{{ route('admin.tiers.create') }}"
           class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
            + Add Tier
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Tier Name</th>
                    <th class="p-3">Minimum Points</th>
                    <th class="p-3">Discount</th>
                    <th class="p-3">Members</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tiers as $tier)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">
                            #{{ $tier->tier_id }}
                        </td>

                        <td class="p-3">
                            {{ $tier->tier_name }}

                            @if ($tier->min_points === 0)
                                <span class="ml-2 rounded-full bg-zinc-200 px-2 py-0.5 text-xs dark:bg-zinc-700">
                                    Default for new members
                                </span>
                            @endif
                        </td>

                        <td class="p-3">
                            {{ number_format($tier->min_points) }}
                        </td>

                        <td class="p-3">
                            {{ $tier->discount_percent }}%
                        </td>

                        <td class="p-3">
                            {{ number_format($tier->members_count) }}
                        </td>

                        <td class="p-3">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('admin.tiers.edit', $tier) }}"
                                   class="text-blue-600">
                                    Edit
                                </a>

                                @if ($tier->min_points !== 0)
                                    <form method="POST"
                                          action="{{ route('admin.tiers.destroy', $tier) }}"
                                          data-confirm="Delete tier {{ $tier->tier_name }}?"
                                          onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="text-red-600">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="p-6 text-center text-zinc-500">
                            No tiers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts::app>
