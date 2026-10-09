@extends('layouts.public')

@section('bleed')
    <x-store.account-shell active="orders" heading="My Orders" subheading="Track the status of your orders and review cars once an order is completed.">
        <x-store.flash />
        @include('orders.partials.list', ['orders' => $orders, 'reviewableOrderIds' => $reviewableOrderIds])
    </x-store.account-shell>
@endsection
