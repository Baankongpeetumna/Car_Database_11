<x-layouts::app :title="'Manage Orders'">
    @include('commerce.messages')

    @php
        $labels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Manage Orders
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <form method="GET"
          action="{{ route('admin.orders.index') }}"
          class="my-5 flex gap-3">
        <select
            name="status"
            aria-label="Filter by status"
            class="rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800"
        >
            <option value="">All statuses</option>

            @foreach ($labels as $value => $label)
                <option value="{{ $value }}"
                        @selected(request('status') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white">
            Filter
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">Order No.</th>
                    <th class="p-3">Member</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">
                            #{{ $order->order_id }}
                        </td>

                        <td class="p-3">
                            {{ $order->member?->name }}
                            <br>
                            {{ $order->member?->email }}
                        </td>

                        <td class="p-3">
                            {{ $order->order_date?->format('d/m/Y H:i') }}
                        </td>

                        <td class="p-3">
                            ฿{{ \App\Support\Money::display(
                                \App\Support\Money::cents($order->total_amount)
                            ) }}
                        </td>

                        <td class="p-3">
                            {{ $labels[$order->status] ?? $order->status }}
                        </td>

                        <td class="p-3">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="text-blue-600">
                                View / Update Status
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="p-6 text-center text-zinc-500">
                            No orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</x-layouts::app>
