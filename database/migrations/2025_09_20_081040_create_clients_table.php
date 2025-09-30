<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id('id'); // Client_ID
            $table->string('name'); // Client_Name
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('type', ['Paid', 'Free'])->default('Free');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin')->nullable();
            $table->string('gst_no')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};

