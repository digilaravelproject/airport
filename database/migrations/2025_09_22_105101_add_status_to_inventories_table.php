<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('photo'); // 1=Active, 0=Inactive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
