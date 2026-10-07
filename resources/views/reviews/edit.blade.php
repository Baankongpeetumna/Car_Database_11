@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <a href="{{ route('products.show', $review->car_id) }}#reviews"
       class="mb-4 inline-block text-blue-600">
        ← Back to {{ $review->car?->model_name }}
    </a>

    <h1 class="mb-4 text-2xl font-semibold">
        Edit Your Review
    </h1>

    <form method="POST"
          action="{{ route('reviews.update', $review) }}"
          class="max-w-2xl space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        @csrf
        @method('PUT')

        @include('reviews._form-fields', ['review' => $review])

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2 text-white">
            Save Changes
        </button>
    </form>
@endsection
