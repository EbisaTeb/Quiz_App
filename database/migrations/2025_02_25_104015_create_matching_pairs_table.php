 <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up()
        {
            Schema::create('matching_pairs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
                $table->text('left_value');
                $table->text('right_value');
                $table->timestamps();
            });
        }

        public function down()
        {
            Schema::dropIfExists('matching_pairs');
        }
    };
