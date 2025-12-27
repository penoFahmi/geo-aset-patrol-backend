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
    Schema::create('regions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('type', ['kota', 'kecamatan', 'kelurahan', 'zona_patroli']);
        $table->longText('geojson_data');
        $table->string('fill_color')->default('#3388ff');
        $table->string('stroke_color')->default('#3388ff');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
