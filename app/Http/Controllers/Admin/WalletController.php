<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use IntaSend\IntaSendPHP\Wallet;

class WalletController extends Controller
{
    public function index()
    {
        $credentials = [
            'token' => env('INTASEND_SECRET_KEY'),
            'publishable_key' => env('INTASEND_PUBLIC_KEY'),
            'test' => env('APP_ENV') !== 'production',
        ];
        $wallet = new Wallet();
        $wallet->init($credentials);
        $wallets = $wallet->retrieve();
        return response()->json($wallets);
    }

    public function show($walletId)
    {
        $credentials = [
            'token' => env('INTASEND_SECRET_KEY'),
            'publishable_key' => env('INTASEND_PUBLIC_KEY'),
            'test' => env('APP_ENV') !== 'production',
        ];
        $wallet = new Wallet();
        $wallet->init($credentials);
        $details = $wallet->details($walletId);
        return response()->json($details);
    }
} 