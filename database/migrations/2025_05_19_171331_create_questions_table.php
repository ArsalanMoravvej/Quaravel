<?php

use App\Models\Survey;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Survey::class)->constrained()->cascadeOnDelete();
            $table->string('title', 1500);
            $table->unsignedTinyInteger('type');

            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();

            $table->decimal('min_value', places: 3)->nullable();
            $table->decimal('max_value', places: 3)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
