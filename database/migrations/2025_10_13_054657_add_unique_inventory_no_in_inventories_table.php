<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('inventories','box_serial_no')) return;
            $table->unique('box_serial_no', 'inventories_box_serial_no_unique');
        });
    }
    public function down(): void {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('inventories_box_serial_no_unique');
        });
    }
};

