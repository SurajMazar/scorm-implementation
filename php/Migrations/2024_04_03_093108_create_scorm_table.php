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
        Schema::create('scorm', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->nullableMorphs('model');
            $table->string('title')->nullable();
            $table->string('version')->nullable();
            $table->string('hash_name')->nullable();
            $table->string('origin_file')->nullable();
            $table->string('origin_file_mime')->nullable();
            $table->string('entry_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorm');
    }
};
