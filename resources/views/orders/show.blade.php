@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <a href="{{ route('orders.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← ประวัติคำสั่งซื้อ
    </a>

    @include('commerce.order-details')
@endsection