<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();

            $table->string('material')->nullable();
            $table->string('color')->nullable();

            $table->json('attributes')->nullable();

            $table->boolean('is_configurable')->default(false);
            $table->boolean('is_photo_printable')->default(false);
            $table->boolean('has_qr_memorial')->default(false);

            $table->boolean('is_kit')->default(false);
            $table->unsignedInteger('included_units')->nullable();
            $table->unsignedInteger('extra_unit_price')->nullable();

            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index(['material', 'color']);
            $table->index('price');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
