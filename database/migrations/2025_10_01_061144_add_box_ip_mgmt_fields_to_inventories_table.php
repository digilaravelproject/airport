<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('box_ip')->nullable()->after('photo');
            $table->string('mgmt_url')->nullable()->after('box_ip');
            $table->string('mgmt_token')->nullable()->after('mgmt_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['box_ip', 'mgmt_url', 'mgmt_token']);
        });
    }
};
