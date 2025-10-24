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
            $table->string('terminal')->nullable()->after('location');
            $table->string('level')->nullable()->after('terminal');
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['terminal','level']);
        });
    }
};
