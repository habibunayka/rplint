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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('generation_class_id')->constrained('generation_classes')->onDelete('cascade');
            $table->integer('absence');
            $table->bigInteger('nis')->unique();
            $table->bigInteger('nisn')->unique();
            $table->string('profile_photo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->longText('fingerprint_template1')->nullable();
            $table->longText('fingerprint_template2')->nullable();
            $table->longText('fingerprint_template3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
