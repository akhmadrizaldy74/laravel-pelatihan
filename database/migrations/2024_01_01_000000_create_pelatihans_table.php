<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pelatihans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pelatihan');
            $table->integer('jumlah_peserta');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['Belum Dimulai', 'Sedang Berlangsung', 'Tahap Seleksi', 'Selesai'])->default('Belum Dimulai');
            $table->boolean('auto_status')->default(true)->comment('If true, status is auto-calculated from dates');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihans');
    }
};
