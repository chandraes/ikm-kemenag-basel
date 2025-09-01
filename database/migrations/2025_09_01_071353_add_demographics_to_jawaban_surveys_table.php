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
        Schema::table('jawaban_surveys', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->after('keterangan_keperluan');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('pendidikan')->nullable()->after('agama');
            $table->string('pekerjaan')->nullable()->after('pendidikan');
            $table->string('pekerjaan_lainnya')->nullable()->after('pekerjaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_surveys', function (Blueprint $table) {
                $table->dropColumn([
                'jenis_kelamin',
                'agama',
                'pendidikan',
                'pekerjaan',
                'pekerjaan_lainnya',
            ]);
        });
    }
};
