 <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->dateTime('start_time')->nullable();
                $table->dateTime('end_time')->nullable();
                $table->unsignedInteger('time_limit');  // In minutes
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->foreignId('published_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->index(['subject_id', 'teacher_id']);
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('quizzes');
        }
    };
