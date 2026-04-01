<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('author');
            $table->tinyInteger('rating');
            $table->text('body');
            $table->timestamps();

            $table->index(['product_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
