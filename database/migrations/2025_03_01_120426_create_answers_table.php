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
            Schema::create('answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attempt_id')->constrained('quiz_attempts')->onDelete('cascade');
                $table->foreignId('question_id')->constrained()->onDelete('cascade');
                $table->json('student_answer')->nullable(); // Allow NULL for unanswered questions
                $table->boolean('is_correct')->default(false);
                $table->decimal('marks_obtained', 5, 2)->default(0);
                $table->timestamps();

                // Prevent duplicate answers per attempt
                $table->unique(['attempt_id', 'question_id']);
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('answers');
        }
    };
