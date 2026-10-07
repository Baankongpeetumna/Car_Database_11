<x-layouts::app :title="'Activity Log'">
    @include('commerce.messages')

    @php
        $field = 'rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800';

        $actionStyles = [
            'created' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'updated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            'restocked' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'status_changed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            'role_changed' => 'bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200',
        ];

        // แสดงค่าใน log ให้อ่านง่าย
        $show = function ($value) {
            if ($value === null || $value === '') {
                return '—';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return Str::limit(is_scalar($value) ? (string) $value : json_encode($value), 80);
        };
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Activity Log
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <p class="mt-3 text-sm text-zinc-500">
        Every change an admin makes to cars, brands, categories, membership tiers, reviews and order status.
    </p>

    <form method="GET"
          action="{{ route('admin.logs.index') }}"
          class="my-5 flex flex-wrap gap-3">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search name (e.g. Camry)"
            aria-label="Search name"
            class="{{ $field }}"
        >

        <select name="subject" aria-label="Table" class="{{ $field }}">
            <option value="">All tables</option>

            @foreach ($subjects as $value => $label)
                <option value="{{ $value }}" @selected(request('subject') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="action" aria-label="Action" class="{{ $field }}">
            <option value="">All actions</option>

            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>
                    {{ Str::headline($action) }}
                </option>
            @endforeach
        </select>

        <select name="admin" aria-label="Admin" class="{{ $field }}">
            <option value="">All admins</option>

            @foreach ($admins as $admin)
                <option value="{{ $admin->member_id }}"
                        @selected((string) request('admin') === (string) $admin->member_id)>
                    {{ $admin->name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white">
            Filter
        </button>

        <a href="{{ route('admin.logs.index') }}"
           class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-600">
            Reset
        </a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">Time</th>
                    <th class="p-3">Admin</th>
                    <th class="p-3">Action</th>
                    <th class="p-3">Item</th>
                    <th class="p-3">Changes</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-zinc-200 align-top dark:border-zinc-700">
                        <td class="whitespace-nowrap p-3">
                            {{ $log->created_at?->format('d/m/Y H:i:s') }}
                        </td>

                        <td class="p-3">
                            {{-- member_id ว่าง = ทำจาก terminal (php artisan member:role) --}}
                            @if ($log->member_id === null)
                                <span class="text-zinc-500">System (terminal)</span>
                            @else
                                {{ $log->member?->name ?? '—' }}
                            @endif
                        </td>

                        <td class="p-3">
                            <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $actionStyles[$log->action] ?? '' }}">
                                {{ Str::headline($log->action) }}
                            </span>
                        </td>

                        <td class="p-3">
                            <span class="text-zinc-500">
                                {{ $subjects[$log->subject_type] ?? $log->subject_type }} #{{ $log->subject_id }}
                            </span>
                            <br>
                            {{ $log->description }}
                        </td>

                        <td class="p-3">
                            @if ($log->changes)
                                <ul class="space-y-0.5">
                                    @foreach ($log->changes as $column => [$old, $new])
                                        <li>
                                            <span class="font-mono text-xs text-zinc-500">{{ $column }}:</span>

                                            @if ($log->action === 'created')
                                                {{ $show($new) }}
                                            @elseif ($log->action === 'deleted')
                                                <span class="line-through">{{ $show($old) }}</span>
                                            @else
                                                <span class="text-red-600 line-through">{{ $show($old) }}</span>
                                                → <span class="text-green-700 dark:text-green-400">{{ $show($new) }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="p-6 text-center text-zinc-500">
                            No activity found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</x-layouts::app>
