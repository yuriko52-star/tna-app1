<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeEditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_time_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_time_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('request_date');
            $table->date('target_date');
            $table->dateTime('new_clock_in')->nullable();
            $table->dateTime('new_clock_out')->nullable();
            $table->text('reason');
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('break_time_edits');
    }
}
