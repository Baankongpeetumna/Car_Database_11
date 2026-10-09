{{-- แจ้งผลสำเร็จ / ข้อผิดพลาด ใช้ร่วมกันทุกหน้า --}}
@if (session('success') || session('status'))
    <div role="status" {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800']) }}>
        <x-store.icon name="check-circle" class="mt-0.5 size-5 text-emerald-600" />
        <p>{{ session('success') ?? session('status') }}</p>
    </div>
@endif

@if ($errors->any())
    <div role="alert" {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-3 rounded-2xl border border-race/30 bg-race-soft px-4 py-3 text-sm text-race-dark']) }}>
        <x-store.icon name="alert" class="mt-0.5 size-5 text-race" />
        <div class="space-y-0.5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
