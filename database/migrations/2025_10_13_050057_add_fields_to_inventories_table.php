<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('box_subnet')->nullable()->after('box_mac');
            $table->string('gateway')->nullable()->after('box_subnet');
            $table->string('box_os')->nullable()->after('gateway');
            $table->string('supplier_name')->nullable()->after('box_os');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['box_subnet', 'gateway', 'box_os_supplier_name']);
        });
    }
};
