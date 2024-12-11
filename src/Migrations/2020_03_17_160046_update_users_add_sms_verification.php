<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersAddSmsVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //$table->string('phone')->default('');
            $table->string('sms_verification_code')->default('');
            $table->boolean('sms_verification_status')->default(false);
            $table->bigInteger('sms_verification_attempts')->default(0);
            $table->datetime('sms_verification_expires_at')->nullable();
            $table->string('country')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sms_verification_number');
            $table->dropColumn('sms_verification_code');
            $table->dropColumn('sms_verification_status');
            $table->dropColumn('sms_verification_attempts');
            $table->dropColumn('sms_verification_expires_at');
            $table->dropColumn('country');
        });
    }
}
