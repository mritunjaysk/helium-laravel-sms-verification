<?php


namespace Helium\SMSVerification\Exceptions;

class PhoneNumberNotDefinedException extends \Exception
{

    protected $message;

    /**
     * PhoneNumberNotDefinedException constructor.
     */
    public function __construct()
    {
        $this->message = trans('sms_verification.error_missing_phone');
    }
}
