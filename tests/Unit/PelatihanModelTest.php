<?php

namespace Tests\Unit;

use App\Models\MasterMetode;
use App\Models\Pelatihan;
use App\Services\PelatihanImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelatihanModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_computed_status_respects_custom_seleksi_hari(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1));

        $p = new Pelatihan([
            'tanggal_mulai'   => '2026-05-15',
            'tanggal_selesai' => '2026-05-20',
            'auto_status'     => true,
            'seleksi_hari'    => 20, // H-20 -> starts 2026-04-25
        ]);

        $this->assertSame('Tahap Seleksi', $p->computed_status);

        $p->seleksi_hari = 7; // H-7 -> starts 2026-05-08
        $this->assertSame('Belum Dimulai', $p->computed_status);

        Carbon::setTestNow();
    }

    public function test_calculate_position_clamps_cross_year_dates(): void
    {
        $pos = Pelatihan::calculatePosition(
            Carbon::create(2025, 12, 20),
            Carbon::create(2026, 1, 15),
            2026
        );

        $this->assertNotNull($pos);
        $this->assertSame('0.00%', $pos['left']);

        $outOfYear = Pelatihan::calculatePosition(
            Carbon::create(2025, 1, 1),
            Carbon::create(2025, 2, 1),
            2026
        );
        $this->assertNull($outOfYear);
    }

    public function test_master_metode_color_is_resolved_dynamically(): void
    {
        MasterMetode::create([
            'nama'       => 'Coaching Lapangan',
            'kode_warna' => '#14b8a6',
            'warna_teks' => '#042f2e',
            'urutan'     => 1,
            'is_active'  => true,
        ]);

        $this->assertSame('#14b8a6', Pelatihan::getMetodeColor('Coaching Lapangan'));
        $this->assertSame('#042f2e', Pelatihan::getMetodeTextColor('Coaching Lapangan'));
    }

    public function test_import_service_parses_multi_stage_tahapan_string(): void
    {
        $raw = 'Mandiri/MOOC: 2026-09-14 s/d 2026-09-28; Klasikal: 2026-10-28 s/d 2026-11-11';
        $parsed = PelatihanImportService::parseTahapan($raw, 2026);

        $this->assertCount(2, $parsed);
        $this->assertSame('Mandiri/MOOC', $parsed[0]['tahap']);
        $this->assertSame('2026-09-14', $parsed[0]['mulai']);
        $this->assertSame('2026-09-28', $parsed[0]['selesai']);
        $this->assertSame('Klasikal', $parsed[1]['tahap']);
        $this->assertSame('2026-11-11', $parsed[1]['selesai']);
    }
}
