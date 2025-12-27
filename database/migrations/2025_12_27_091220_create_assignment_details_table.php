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
    Schema::create('assignment_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
        $table->foreignId('asset_id')->constrained('assets');
        $table->integer('sequence_order')->default(0);
        $table->boolean('is_visited')->default(false);
        $table->timestamp('visited_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_details');
    }
};
