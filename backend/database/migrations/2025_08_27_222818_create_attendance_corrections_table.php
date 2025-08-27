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
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->date('start_date');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->integer('total_breaking_time');
            $table->integer('actual_working_time');
            $table->string('comment', 255);
            $table->timestamp('correction_request_date');
            $table->timestamp('approval_date')->nullable();
            $table->tinyInteger('state');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
