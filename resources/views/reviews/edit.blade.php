@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('products.show', $review->car_id) }}#reviews" class="inline-flex items-center gap-1 text-sm font-semibold text-zinc-500 hover:text-ink">
            <x-store.icon name="arrow-left" class="size-4" /> Back to {{ $review->car?->model_name }}
        </a>

        <h1 class="mt-2 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Edit Your Review</h1>
        <p class="mt-2 text-sm text-zinc-500">{{ $review->car?->model_name }} · {{ $review->car?->model_year }}</p>

        <div class="mt-6">
            <x-store.flash />
        </div>

        <form method="POST" action="{{ route('reviews.update', $review) }}" class="space-y-5 rounded-3xl border border-line bg-white p-6">
            @csrf
            @method('PUT')

            @include('reviews._form-fields', ['review' => $review])

            <div class="flex justify-end gap-2 border-t border-line pt-5">
                <a href="{{ route('products.show', $review->car_id) }}#reviews" class="rounded-xl border border-line px-5 py-2.5 text-sm font-semibold text-ink hover:border-ink">Cancel</a>
                <button type="submit" class="rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
