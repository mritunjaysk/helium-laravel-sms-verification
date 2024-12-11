<?php

namespace Helium\SMSVerification\Traits;

use Helium\SMSVerification\Exceptions\PhoneNumberNotDefinedException;
use GuzzleHttp\Client;
use Helium\SMSVerification\Exceptions\TimeLimitExceeded;
use Helium\SMSVerification\Exceptions\TooManySMSVerificationAttempts;
use Helium\SMSVerification\HeliumSNSClientFacade;
use Helium\StringHelpers\StringHelpers;
use Illuminate\Support\Facades\Hash;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Trait VerifiesSMSCode
 *
 * Sends an SMS message containing a verificaiton code to a given mobile number (using AWS SNS)
 * Contains methods for verifying the user submitted code (authorising action) and monitoring attempts.
 *
 * @package Freelabois\SMSVerification\Traits
 */
trait VerifiesSMSCode
{
    protected $sms_verification_attempt_limit = 3;
    protected $sms_verification_time_limit = 10;

    protected function setSMSVerificationCodeAttribute($value){
        $this->attribute['sms_verification_code'] = Hash::make($value);
    }

    public function getFormattedPhoneAttribute(){
        return (string) PhoneNumber::make($this->phone, $this->country);
    }

    /**
     * Sends the SMS confirmation code
     *
     * @param $mobile
     * @return $this
     */
    public function setSMSVerificationNumber($mobile)
    {
        $code = $this->getNewCode();

       HeliumSNSClientFacade::publish([
            "SenderId"    => $this->getSMSVerificationSender(),
            "SMSType"     => "Transactional",
            "Message"     => $this->getSMSVerificationMessage($code),
            "PhoneNumber" => $mobile,
        ]);

        $this->sms_verification_code   = $code;
        $this->sms_verification_status = false;
        $this->sms_verification_attempts = 1;
        $this->sms_verification_expires_at = \Carbon\Carbon::now()->addMinutes($this->sms_verification_time_limit);

        if ($this->SMSVerificationAttemptLimitEnabled()) {
            $this->sms_verification_attempts = 1;
        }

        $this->save();

        return $this;
    }

    /**
     * Verifies the submitted SMS code
     *
     * @param $code
     * @return bool
     * @throws \Exception
     */
    public function verifySMSCode($code)
    {
        return $this
            ->validateSMSVerificationAttempts()
            ->setSMSVerificationStatus(Hash::check($code, $this->sms_verification_code));
    }


    /**
     * Validates SMS Verification attempts
     *
     * @return $this
     * @throws TooManySMSVerificationAttempts
     */
    private function validateSMSVerificationAttempts()
    {
        if ( $this->SMSVerificationAttemptLimitEnabled() && $this->SMSVerificationAttemptLimitExceeded()) {
            throw new TooManySMSVerificationAttempts(trans('sms_verification.attempts_exceeded'));
        }

        if ($this->SMSVerificationTimeLimitExceeded()) {
            throw new TimeLimitExceeded(trans('sms_verification.time_limit_exceeded'));
        }

        return $this;
    }

    /**
     * Validates SMS Verification attempts
     *
     * @return $this
     * @throws TimeLimitExceeded
     */
    private function SMSVerificationTimeLimitExceeded()
    {
        return \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($this->sms_verification_expires_at));
    }


    /**
     * Checks it SMS Verification attempt limit is enabled
     *
     * @return bool
     */
    private function SMSVerificationAttemptLimitEnabled()
    {
        return !empty($this->sms_verification_attempt_limit);
    }

    /**
     * Checks if SMS Verification attempt limit exceeded
     *
     * @return bool
     */
    private function SMSVerificationAttemptLimitExceeded()
    {
        return $this->sms_verification_attempts > $this->sms_verification_attempt_limit;
    }

    /**
     * Sets the SMS verification status
     *
     * @param bool $status
     * @return bool
     */
    private function setSMSVerificationStatus(bool $status)
    {
        $this->sms_verification_status = $status;

        $this
            ->updateSMSVerificationAttempts($status)
            ->save();

        return $status;
    }

    /**
     * Updates the SMS Verification attempts
     *
     * @param bool $status
     * @return $this
     */
    private function updateSMSVerificationAttempts(bool $status)
    {
        if ($this->SMSVerificationAttemptLimitEnabled() && !$status) {
            $this->sms_verification_attempts++;
        }

        return $this;
    }

    /**
     * Gets the message to be sent with the SMS
     *
     * @param $code
     * @return string
     */
    protected function getSMSVerificationMessage($code)
    {
        return "Your SMS verification code is: $code";
    }

    public function sendSMSVerificationCode()
    {
        if (!empty($this->phone)) {
            $this->setSMSVerificationNumber($this->formatted_phone);
        }else{
            throw new PhoneNumberNotDefinedException();
        }

    }

    /**
     * Gets the sender of the verification SMS
     *
     * @return string
     */
    protected function getSMSVerificationSender()
    {
        return env('APP_NAME');
    }
    
    protected function getNewCode()
    {
        return StringHelpers::randomNumericalToken(6);
    }
}
