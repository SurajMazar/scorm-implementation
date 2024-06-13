<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scorm_sco', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->bigInteger('scorm_id')->unsigned()->nullable();
            $table->foreign('scorm_id')->references('id')->on('scorm')->onDelete('cascade');
            $table->bigInteger('sco_parent_id')->unsigned()->nullable();
            $table->string('entry_url')->nullable();
            $table->string('identifier')->nullable();
            $table->string('title')->nullable();
            $table->tinyInteger('visible')->nullable();
            $table->longText('sco_parameters')->nullable();
            $table->longText('launch_data')->nullable();
            $table->string('max_time_allowed')->nullable();
            $table->string('time_limit_action')->nullable();
            $table->tinyInteger('block')->nullable();
            $table->integer('score_int')->nullable();
            $table->decimal('score_decimal', 10, 7)->nullable();
            $table->decimal('completion_threshold', 10, 7)->nullable();
            $table->string('prerequisites')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorm_sco');
    }
};
