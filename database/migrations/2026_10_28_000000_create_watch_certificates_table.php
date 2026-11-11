<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watch_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('watch_id')->constrained();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('serial_number')->unique();
            $table->string('certificate_hash')->unique();
            $table->unsignedBigInteger('block_index')->nullable();
            $table->string('block_hash')->nullable();
            $table->string('node_url')->nullable();
            $table->string('status')->default('pending'); // pending, mined, verified, failed
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_certificates');
    }
};
