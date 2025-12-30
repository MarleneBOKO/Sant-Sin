<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(true)->after('password');
            $table->dateTime('password_changed_at', 7)->nullable()->after('must_change_password'); // datetime2(7)
            $table->boolean('password_expired')->default(false)->after('password_changed_at');
            $table->dateTime('password_expiry_notified_at', 7)->nullable()->after('password_expired'); // datetime2(7)
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'password_changed_at',
                'password_expired',
                'password_expiry_notified_at'
            ]);
        });
    }
};
