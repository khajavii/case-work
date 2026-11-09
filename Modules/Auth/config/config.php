<?php

return [
    'name' => 'Auth',

    'otp' => [
        /*
         * A fixed OTP code is used until an SMS provider is integrated.
         * Replace this value with a generated/random code when needed.
         */
        'code' => env('AUTH_OTP_CODE', '123456'),
        'max_attempts' => (int) env('AUTH_OTP_MAX_ATTEMPTS', 5),
    ],
];
