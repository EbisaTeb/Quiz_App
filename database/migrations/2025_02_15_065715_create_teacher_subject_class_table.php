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
            //   Teacher-Subject-Class Relationships
            Schema::create('teacher_subject_class', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['teacher_id', 'subject_id', 'class_id']);
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('teacher_subject_class');
        }
    };
