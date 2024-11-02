<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('packaging_type')->nullable(); // Tipo de embalaje
            $table->string('material')->nullable();       // Material
            $table->string('usage_location')->nullable(); // Lugar de uso
            $table->string('color')->nullable();          // Color
            $table->string('load_capacity')->nullable();  // Capacidad de carga
            $table->string('country_of_origin')->nullable(); // País de origen
            $table->boolean('warranty')->default(false);  // Garantía
            $table->integer('number_of_pieces')->default(1); // Número de piezas
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
