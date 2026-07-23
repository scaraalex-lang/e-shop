<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('label');
            $table->string('type')->default('text');
            $table->json('options')->nullable();

            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['category_id', 'key']);
            $table->index(['category_id', 'is_filterable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_definitions');
    }
};
