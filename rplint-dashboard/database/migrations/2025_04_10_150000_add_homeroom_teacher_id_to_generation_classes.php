<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generation_classes', function (Blueprint $table) {
            $table->foreignId('homeroom_teacher_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('generation_classes', function (Blueprint $table) {
            $table->dropForeign(['homeroom_teacher_id']);
            $table->dropColumn('homeroom_teacher_id');
        });
    }
};
