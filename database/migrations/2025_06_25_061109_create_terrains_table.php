<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terrains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->decimal('area_size', 10, 2);
            $table->decimal('price_per_day', 10, 2);
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('main_image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('terrains');
    }
};