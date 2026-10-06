@if (session('success'))
    <div role="status"
         class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert"
         class="mb-4 rounded-lg bg-red-100 p-3 text-sm text-red-800">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif