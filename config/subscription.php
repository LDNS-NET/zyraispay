<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the subscription system.
    | You can customize trial periods, billing amounts, and other settings here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Trial Period
    |--------------------------------------------------------------------------
    |
    | The number of days a new tenant gets for free trial.
    |
    */
    'trial_days' => env('SUBSCRIPTION_TRIAL_DAYS', 10),

    /*
    |--------------------------------------------------------------------------
    | Billing Period
    |--------------------------------------------------------------------------
    |
    | The number of days for each billing cycle.
    |
    */
    'billing_period_days' => env('SUBSCRIPTION_BILLING_PERIOD_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Monthly Amount
    |--------------------------------------------------------------------------
    |
    | The default monthly subscription amount in KES.
    |
    */
    'monthly_amount' => env('SUBSCRIPTION_MONTHLY_AMOUNT', 5000.00),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for subscriptions.
    |
    */
    'currency' => env('SUBSCRIPTION_CURRENCY', 'KES'),

    /*
    |--------------------------------------------------------------------------
    | Payment Method
    |--------------------------------------------------------------------------
    |
    | The default payment method for subscriptions.
    |
    */
    'payment_method' => env('SUBSCRIPTION_PAYMENT_METHOD', 'mpesa'),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | The number of days after expiration before completely blocking access.
    |
    */
    'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Auto-renewal
    |--------------------------------------------------------------------------
    |
    | Whether subscriptions should auto-renew.
    |
    */
    'auto_renewal' => env('SUBSCRIPTION_AUTO_RENEWAL', false),

    /*
    |--------------------------------------------------------------------------
    | Reminder Days
    |--------------------------------------------------------------------------
    |
    | Number of days before expiration to send reminders.
    |
    */
    'reminder_days' => env('SUBSCRIPTION_REMINDER_DAYS', 3),
];
