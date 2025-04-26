<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyBreakTimeEditsForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('break_time_edits', function (Blueprint $table) {
        $table->dropForeign(['break_time_id']);
       
        $table->foreign('break_time_id')
              ->references('id')
              ->on('break_times');
              
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('break_time_edits', function (Blueprint $table) {
        $table->dropForeign(['break_time_id']);
        
        $table->foreign('break_time_id')
              ->references('id')
              ->on('break_times')
              ->cascadeOnDelete();
    });
    }
}
