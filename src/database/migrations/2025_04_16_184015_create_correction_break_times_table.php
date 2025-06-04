<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('break_time_id')->nullable()->constrained()->nullOnDelete();
            $table->time('break_start');
            $table->time('break_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correction_break_times');
    }
}
