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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->time('total_breaking_time')->nullable();
            $table->time('total_working_time')->nullable();
            $table->timestamp('corrected_start_time')->nullable();
            $table->timestamp('corrected_end_time')->nullable();
            $table->string('comment', 255)->nullable();
            $table->boolean('is_correction_request')->nullable();
            $table->timestamp('correction_request_date')->nullable();
            $table->boolean('is_approval')->nullable();
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
        Schema::dropIfExists('attendances');
    }
};
