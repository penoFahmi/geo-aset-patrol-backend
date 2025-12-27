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
    Schema::create('patrol_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('assignment_detail_id')->constrained('assignment_details')->onDelete('cascade');
        $table->double('latitude');
        $table->double('longitude');
        $table->double('distance_deviation')->default(0);
        $table->boolean('is_valid_radius')->default(false);
        $table->string('photo_path');
        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrol_reports');
    }
};
