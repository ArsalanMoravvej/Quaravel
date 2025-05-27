<?php

use App\Models\Survey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->unsignedTinyInteger('type');
            $table->string('title', 1500);
            $table->text('description')->nullable();
            $table->boolean('allow_multiple_select')->default(false);
            $table->boolean('alphabetical_order')->default(false);
            $table->boolean('answer_required')->default(false);
            $table->boolean('randomized')->default(false);
            $table->string('placeholder', 100)->nullable();

            // For QuestionType::Text
            $table->integer('answer_min_length')->nullable();
            $table->integer('answer_max_length')->nullable();

            // For QuestionType::MultipleChoice
            $table->integer('min_selectable_choices')->nullable();
            $table->integer('max_selectable_choices')->nullable();

            // For QuestionType::Numeral
            $table->boolean('allow_decimals')->default(false);
            $table->decimal('number_min_value', places: 3)->nullable();
            $table->decimal('number_max_value', places: 3)->nullable();

            // For QuestionType::OpinionScale & QuestionType::Rating
            $table->integer('steps')->nullable();

            // For QuestionType::OpinionScale
            $table->boolean('start_from_zero')->default(false);
            $table->boolean('negative_scale')->default(false);
            $table->string('left_label', 40)->nullable();
            $table->string('center_label', 40)->nullable();
            $table->string('right_label', 40)->nullable();

            //For QuestionType::Rating
            $table->unsignedTinyInteger('rating_type')->default(1)->comment('1=Stars, 2=Hearts ,3=ThumbsUps, 4=PileOfPoo');

            //For QuestionType::Ranking
            $table->boolean('allow_tied')->default(false);

            $table->softDeletes();
            $table->timestamps();

        });

        DB::statement(
            /** @lang text */ '
            ALTER TABLE questions
            ADD CONSTRAINT chk_type
            CHECK (type BETWEEN 1 AND 7)'
        );

        DB::statement(
            /** @lang text */
            'ALTER TABLE questions
            ADD CONSTRAINT chk_rating_type
            CHECK (rating_type BETWEEN 1 AND 4)'
        );

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
