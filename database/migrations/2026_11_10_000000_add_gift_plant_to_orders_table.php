<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('gift_plant_id')->nullable()->after('total');
            $table->string('gift_plant_name')->nullable()->after('gift_plant_id');
            $table->string('gift_plant_category')->nullable()->after('gift_plant_name');
            $table->string('gift_plant_image')->nullable()->after('gift_plant_category');
            $table->string('gift_plant_detail_url')->nullable()->after('gift_plant_image');
            $table->unsignedInteger('gift_plant_price')->nullable()->after('gift_plant_detail_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'gift_plant_id',
                'gift_plant_name',
                'gift_plant_category',
                'gift_plant_image',
                'gift_plant_detail_url',
                'gift_plant_price',
            ]);
        });
    }
};
