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
    Schema::create('assets', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('address');
        $table->double('area_size')->default(0);
        $table->enum('status', ['aman', 'sengketa', 'tanah_pemda', 'sewa'])->default('aman');
        $table->longText('geojson_data');
        $table->double('centroid_lat');
        $table->double('centroid_lng');
        $table->string('image_path')->nullable();
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
