<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Payment\CreateRequest;
use App\Models\MeetingPack;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function select(): View
    {
        $plans = MeetingPack::published()->ordered()->get();

        return view('meeting-quota.checkout-select', compact('plans'));
    }

    public function create(CreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // 公開中の面談パックのみ購入可能
        $pack = MeetingPack::published()->findOrFail($validated['meeting_pack_id']);

        // 決済情報を保存
        $payment = Payment::create([
            'user_id' => Auth::id(),
            'meeting_pack_id' => $pack->id,
            'quantity' => $pack->meeting_count,
            'amount' => $pack->price,
            'status' => 'pending',
        ]);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $pack->name,
                        ],
                        'unit_amount' => $pack->price,
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'payment_id' => (string) $payment->id,
            ],
            'success_url' => route('meeting-quota.success'),
            'cancel_url' => route('meeting-quota.checkout.select'),
        ]);

        // CheckoutセッションIDを保存
        $payment->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');

        $payment = Payment::where(
            'stripe_checkout_session_id',
            $sessionId
        )
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('meeting-quota.success', compact('payment'));
    }
}
