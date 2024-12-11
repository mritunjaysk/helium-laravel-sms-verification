<?php


return [
    'app_verification_message' => 'Your '. env('APP_NAME') . '  verification code is here: :code',
    'error_missing_phone' => 'The phone number is missing from the user profile.',
    'time_limit_exceeded' => 'The SMS verification time-limit has been exceeded. Please re-send the SMS code.',
    'attempts_exceeded' => 'Too many SMS verification attempts. Please re-send the SMS code.'
];
