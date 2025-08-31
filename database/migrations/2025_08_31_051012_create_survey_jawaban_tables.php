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
        Schema::create('jawaban_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satker_id')->constrained()->onDelete('cascade');
            $table->string('nama');
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('usia');
            $table->text('alamat_lengkap');
            $table->text('keterangan_keperluan');
            $table->text('kritik_saran')->nullable();
            $table->timestamps();
        });

         Schema::create('jawaban_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('kuesioner_id')->constrained()->onDelete('cascade');
            $table->foreignId('satker_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('jawaban_nilai');
            $table->string('jawaban_label');
            $table->timestamps();

            // Index untuk performa query super cepat!
            $table->index('satker_id');
            $table->index('kuesioner_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('jawaban_items');
        Schema::dropIfExists('jawaban_surveys');
    }
};
