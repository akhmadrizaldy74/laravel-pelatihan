<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->string('kategori')->default('PELATIHAN FUNGSIONAL')->after('id');
            $table->string('durasi')->nullable()->after('jumlah_peserta');
            $table->string('sumber_dana')->default('RM')->after('tanggal_selesai');
            $table->string('tempat_pelaksanaan')->default('PPSDM')->after('sumber_dana');
            $table->string('metode')->default('Klasikal')->after('tempat_pelaksanaan');
            $table->json('jadwal_tahapan')->nullable()->after('metode');
        });
    }

    public function down(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->dropColumn([
                'kategori',
                'durasi',
                'sumber_dana',
                'tempat_pelaksanaan',
                'metode',
                'jadwal_tahapan',
            ]);
        });
    }
};
