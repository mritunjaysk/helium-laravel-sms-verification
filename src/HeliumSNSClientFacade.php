<?php

namespace Helium\SMSVerification;

use Illuminate\Support\Facades\Facade;

class HeliumSNSClientFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'HeliumSNSClientSingleton'; }
}