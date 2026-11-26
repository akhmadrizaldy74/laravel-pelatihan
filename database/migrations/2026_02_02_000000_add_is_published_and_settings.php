<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->index()->after('auto_status');
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('pelatihans', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::dropIfExists('app_settings');
    }
};
