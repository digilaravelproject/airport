<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('locations', function (Blueprint $table) {
            $table->id('id'); // Location_ID
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // relation with Client
            $table->string('location_name'); 
            $table->string('terminal')->nullable();
            $table->string('area')->nullable();
            $table->string('level')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('locations');
    }
};

