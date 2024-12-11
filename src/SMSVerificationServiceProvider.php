<?php

namespace Helium\SMSVerification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Aws\Sns\SnsClient;

class SMSVerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();

        $this->loadTranslationsFrom(__DIR__ . '/Lang', 'sms_verification');

        $this->publishes([
            __DIR__ . '/Lang' => resource_path('lang/vendor/sms_verification'),
        ]);

        Validator::extend('sms_code', function ($attribute, $value, $parameters, $validator) {

            if (empty($parameters)) {

                return Auth::user()->verifySMSCode($value);

            } else {

                return Route::input($parameters[0])->verifySMSCode($value);
            }
        });

        Validator::replacer('sms_code', function ($message, $attribute, $rule, $parameters) {

            return "Invalid code submitted for SMS verification";
        });


    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('HeliumSNSClientSingleton', function (){

            return new SnsClient([
                'version'     => 'latest',
                'region'      => env('AWS_SMS_REGION'),
                'credentials' => [
                    'key'    => env('AWS_SMS_ID'),
                    'secret' => env('AWS_SMS_SECRET'),
                ],
            ]);
        });
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Migrations' => '/database/migrations',
            ]);
        }
    }


}
