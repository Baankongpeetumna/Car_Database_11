<x-layouts::app :title="'Order #'.$order->order_id">
    @include('commerce.messages')

    <a href="{{ route('admin.orders.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← All Orders
    </a>

    <div class="mb-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <p>
            Member:
            {{ $order->member?->name }}
            · {{ $order->member?->email }}
        </p>

        <p>
            Phone:
            {{ $order->member?->phone ?? '-' }}
        </p>

        <p>
            Current points:
            {{ number_format($order->member?->points ?? 0) }}

            · Current tier:
            {{ $order->member?->tier?->tier_name ?? '-' }}
        </p>
    </div>

    @include('commerce.order-details')

    @if (in_array($order->status, ['pending', 'processing'], true))
        <form method="POST"
              action="{{ route('admin.orders.update', $order) }}"
              class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            @csrf
            @method('PATCH')

            <label for="status" class="block font-semibold">
                Change Order Status
            </label>

            <select
                id="status"
                name="status"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
            >
                <option value="">Select new status</option>

                @if ($order->status === 'pending')
                    <option value="processing">
                        Processing
                    </option>
                @endif

                <option value="completed">
                    Completed — add points and update member tier
                </option>

                <option value="cancelled">
                    Cancelled — return stock
                </option>
            </select>

            <p class="text-sm text-zinc-500">
                Select Completed only after payment and car delivery have been verified.
                Completed and Cancelled are final statuses.
            </p>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white">
                Save Status
            </button>
        </form>
    @else
        <p class="rounded-xl border p-4">
            This order is closed. Its status can no longer be changed.
        </p>
    @endif
</x-layouts::app>
