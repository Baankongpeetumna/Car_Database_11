<?php

namespace App\Http\Controllers;

use App\Services\CommerceService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(
        Request $request,
        CommerceService $service
    ): View|RedirectResponse {
        $quote = $service->quote($request->user());

        if ($quote['items'] === []) {
            return redirect()->route('cart.index')
                ->withErrors([
                    'commerce' => 'Please add a car to your cart first.',
                ]);
        }

        // รหัสยืนยันที่เข้ารหัสแล้ว ผูกกับสมาชิกและยอดที่แสดง
        $checkoutToken = Crypt::encryptString(json_encode([
            'id' => (string) Str::uuid(),
            'member_id' => $request->user()->getKey(),
            'fingerprint' => $quote['fingerprint'],
            'expires' => now()->addHour()->timestamp,
        ], JSON_THROW_ON_ERROR));

        return view(
            'checkout.create',
            compact('quote', 'checkoutToken')
        );
    }

    public function store(
        Request $request,
        CommerceService $service
    ): RedirectResponse {
        $input = $request->validate([
            'shipping_address' => ['required', 'string', 'max:5000'],
            'payment_method' => [
                'required',
                Rule::in(['bank_transfer', 'cash']),
            ],
            'checkout_token' => ['required', 'string', 'max:4096'],
        ]);

        $input['shipping_address'] = trim($input['shipping_address']);

        if ($input['shipping_address'] === '') {
            throw ValidationException::withMessages([
                'shipping_address' => 'Please enter a shipping address.',
            ]);
        }

        try {
            $token = json_decode(
                Crypt::decryptString($input['checkout_token']),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (DecryptException|\JsonException $e) {
            throw ValidationException::withMessages([
                'checkout_token' => 'Invalid checkout data. '
                    .'Please reopen the Checkout page.',
            ]);
        }

        if (
            !is_array($token)
            || !is_string($token['id'] ?? null)
            || !Str::isUuid($token['id'])
            || (string) ($token['member_id'] ?? '')
                !== (string) $request->user()->getKey()
            || !is_string($token['fingerprint'] ?? null)
            || !is_int($token['expires'] ?? null)
            || $token['expires'] < now()->timestamp
        ) {
            throw ValidationException::withMessages([
                'checkout_token' => 'Checkout data has expired or is invalid. '
                    .'Please reopen the Checkout page.',
            ]);
        }

        $order = $service->placeOrder(
            $request->user(),
            $input,
            $token
        );

        return redirect()->route('orders.show', $order)
            ->with(
                'success',
                'Order placed. Please contact the store to complete payment.'
            );
    }
}