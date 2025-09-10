<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'login')) {
                $table->string('login')->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->nullable()->after('login');
            }

            if (!Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }

            if (!Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('password');
            }

            if (!Schema::hasColumn('users', 'profil_id')) {
                $table->foreignId('profil_id')->nullable()->constrained('profils')->onDelete('set null')->after('active');
            }

            if (!Schema::hasColumn('users', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null')->after('profil_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profil_id']);
            $table->dropForeign(['service_id']);

            $table->dropColumn([
                'login',
                'nom',
                'prenom',
                'active',
                'profil_id',
                'service_id',
            ]);
        });
    }
};
