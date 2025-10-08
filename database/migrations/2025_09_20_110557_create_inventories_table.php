<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('box_id');
            $table->string('box_model');          // Model e.g. H200
            $table->string('box_serial_no');      // Serial number
            $table->string('box_mac')->unique();  // MAC ID
            $table->string('box_fw')->nullable(); // Firmware
            $table->string('box_remote_model')->nullable(); // RCU Model
            $table->date('warranty_date')->nullable();
            $table->unsignedBigInteger('client_id')->nullable(); // Link with clients table
            $table->string('location')->nullable();
            $table->string('photo')->nullable();  // Image upload
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
