<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infaq_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('masjid_index'); // 1-based, matches $masjids array
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infaq_galleries');
    }
};
