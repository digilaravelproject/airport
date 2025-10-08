<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name');
            $table->string('channel_source_in')->nullable();
            $table->string('channel_source_details')->nullable();
            $table->string('channel_stream_type_out')->nullable();
            $table->string('channel_url')->nullable();
            $table->string('channel_genre')->nullable();
            $table->string('channel_resolution')->nullable();
            $table->string('channel_type')->nullable(); // Paid / Free
            $table->string('language')->nullable(); // ✅ new field
            $table->boolean('encryption')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
