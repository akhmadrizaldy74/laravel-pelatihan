<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->integer('tahun')->default(2026)->index()->after('id');
            $table->string('kode_pelatihan', 50)->nullable()->after('kategori');
            $table->string('angkatan', 80)->nullable()->after('nama_pelatihan');
            $table->integer('peserta_terdaftar')->default(0)->after('jumlah_peserta');
            $table->integer('peserta_lulus')->default(0)->after('peserta_terdaftar');
            $table->integer('jam_pelajaran')->nullable()->after('durasi');
            $table->string('penyelenggara', 120)->nullable()->after('tempat_pelaksanaan');
            $table->string('pic_nama', 100)->nullable()->after('penyelenggara');
            $table->string('pic_kontak', 60)->nullable()->after('pic_nama');
            $table->string('link_pendaftaran', 500)->nullable()->after('pic_kontak');
            $table->text('deskripsi')->nullable()->after('link_pendaftaran');
            $table->integer('seleksi_hari')->default(7)->after('auto_status');
        });
    }

    public function down(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->dropColumn([
                'tahun',
                'kode_pelatihan',
                'angkatan',
                'peserta_terdaftar',
                'peserta_lulus',
                'jam_pelajaran',
                'penyelenggara',
                'pic_nama',
                'pic_kontak',
                'link_pendaftaran',
                'deskripsi',
                'seleksi_hari',
            ]);
        });
    }
};
