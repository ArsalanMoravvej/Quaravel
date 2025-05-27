<?php

use App\Models\Question;
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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['question_id', 'body']);
            $table->unique(['question_id', 'order']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
