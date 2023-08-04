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
        Schema::create('results', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->integer('seat_number')->index()->unique();
            $table->string('school')->index();
            $table->string('education_admin')->index();
            $table->string('student_status')->index();
            $table->string('education_type');
            $table->string('section')->index();

            $table->string('arabic_language');
            $table->string('first_foreign_language');
            $table->string('second_foreign_language');
            $table->string('result_of_pure_math');
            $table->string('history');
            $table->string('geography');
            $table->string('physics');
            $table->string('geology');
            
            $table->string('philosophy');
            $table->string('psychology');
            $table->string('chemistry');
            $table->string('biology');
            $table->string('applied_mathematics');
            
            $table->string('religious_education');
            $table->string('national_education');
            $table->string('economics_and_statistics');

            $table->decimal('total_results',3,2)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
