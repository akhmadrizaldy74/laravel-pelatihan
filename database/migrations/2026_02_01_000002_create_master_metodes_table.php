<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_metodes', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('kode_warna', 20)->default('#60a5fa');
            $table->string('warna_teks', 20)->default('#ffffff');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_metodes');
    }
};
