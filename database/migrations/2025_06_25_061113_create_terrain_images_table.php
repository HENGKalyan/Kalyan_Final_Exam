<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terrain_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terrain_id')->constrained('terrains')->onDelete('cascade');
            $table->string('image_path');
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('terrain_images');
    }
};