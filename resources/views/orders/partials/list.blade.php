{{-- ตารางคำสั่งซื้อของสมาชิก ($orders, $reviewableOrderIds จาก OrderController@index) --}}
@php
    use App\Support\Money;
    $paymentLabels = ['bank_transfer' => 'Bank transfer', 'cash' => 'Pay at the store'];
@endphp

@if ($orders->isEmpty())
    <div class="flex flex-col items-center rounded-3xl border border-dashed border-line bg-white px-6 py-14 text-center">
        <x-store.icon name="clipboard" class="size-10 text-zinc-300" />
        <p class="mt-3 font-display text-xl font-semibold text-ink">You have no orders yet.</p>
        <p class="mt-1 text-sm text-zinc-500">Your orders will appear here once you buy a car.</p>
        <a href="{{ route('products.index') }}" class="mt-5 rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark">Browse cars</a>
    </div>
@else
    <div class="overflow-hidden rounded-3xl border border-line bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-paper text-xs uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Order</th>
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Payment</th>
                        <th class="px-5 py-3 text-right font-semibold">Total</th>
                        <th class="px-5 py-3 text-right font-semibold">Points</th>
                        <th class="px-5 py-3"><span class="sr-only">View</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-paper/60">
                            <td class="px-5 py-4 font-display font-semibold text-ink">#{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $order->order_date?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-store.order-status :status="$order->status" />
                                    @if (in_array($order->order_id, $reviewableOrderIds ?? [], true))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sun px-2.5 py-1 text-xs font-semibold text-ink">★ Review available</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-zinc-600">{{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-ink">฿{{ Money::display(Money::cents((string) $order->total_amount)) }}</td>
                            <td class="px-5 py-4 text-right text-zinc-600">{{ $order->status === 'completed' ? '+'.number_format($order->points_earned) : '-' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-race hover:underline">View <x-store.icon name="chevron-right" class="size-4" /></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (method_exists($orders, 'links'))
        <div class="mt-6">{{ $orders->links('partials.store.pagination') }}</div>
    @endif
@endif
