<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate payment ke Midtrans
     */
    public function createPayment(Request $request, $orderCode)
    {
        try {
            $snapToken = $this->paymentService->initiatePayment($orderCode);
            
            return response()->json([
                'message' => 'Payment initiated successfully',
                'snap_token' => $snapToken
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook/callback dari Midtrans
     */
    public function notificationHandler(Request $request)
    {
        $this->paymentService->handleNotification($request->all());
        
        return response()->json(['message' => 'Callback handled successfully']);
    }
}
