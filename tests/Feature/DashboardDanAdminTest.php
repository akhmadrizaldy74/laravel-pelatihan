<?php

namespace Tests\Feature;

use App\Livewire\Admin\KelolaAdmin;
use App\Livewire\Admin\LoginAdmin;
use App\Livewire\DaftarPelatihan;
use App\Models\AuditLog;
use App\Models\HariLibur;
use App\Models\MasterKategori;
use App\Models\MasterMetode;
use App\Models\Pelatihan;
use App\Models\User;
use App\Services\PelatihanExportService;
use App\Services\PelatihanImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardDanAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_dashboard_filters_by_year_kategori_and_opens_detail(): void
    {
        HariLibur::create([
            'tahun'           => 2027,
            'nama'            => 'Libur Khusus 2027',
            'tanggal_mulai'   => '2027-04-01',
            'tanggal_selesai' => '2027-04-05',
            'keterangan'      => 'Catatan Libur 2027',
            'warna'           => '#b91c1c',
            'is_active'       => true,
        ]);

        $p2026 = Pelatihan::create([
            'tahun'           => 2026,
            'kategori'        => 'PELATIHAN FUNGSIONAL',
            'nama_pelatihan'  => 'JF Pengawas 2026',
            'jumlah_peserta'  => 30,
            'tanggal_mulai'   => '2026-08-01',
            'tanggal_selesai' => '2026-08-10',
        ]);

        $p2027 = Pelatihan::create([
            'tahun'           => 2027,
            'kategori'        => 'PELATIHAN MANAJERIAL',
            'nama_pelatihan'  => 'PKA Angkatan 2027',
            'jumlah_peserta'  => 40,
            'tanggal_mulai'   => '2027-03-01',
            'tanggal_selesai' => '2027-03-20',
            'pic_nama'        => 'Rina',
        ]);

        Livewire::test(DaftarPelatihan::class)
            ->set('tahun', 2027)
            ->assertSee('PKA Angkatan 2027')
            ->assertDontSee('JF Pengawas 2026')
            ->assertSee('Catatan Libur 2027')
            ->call('openDetail', $p2027->id)
            ->assertSee('Rina');

        $this->get(route('public.kalender.print', ['tahun' => 2027]))
            ->assertStatus(200)
            ->assertSee('PKA Angkatan 2027');
    }

    public function test_login_blocks_inactive_users_and_hides_default_password(): void
    {
        $inactive = User::create([
            'name'      => 'Nonaktif',
            'email'     => 'nonaktif@ppsdmk.go.id',
            'password'  => Hash::make('rahasia123'),
            'role'      => User::ROLE_OPERATOR,
            'is_active' => false,
        ]);

        $this->get(route('admin.login'))
            ->assertStatus(200)
            ->assertDontSee('admin123');

        Livewire::test(LoginAdmin::class)
            ->set('email', $inactive->email)
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertSet('errorMessage', 'Akun Anda dinonaktifkan atau tidak memiliki izin akses.');

        $this->assertGuest();
    }

    public function test_admin_can_create_training_with_multi_tahapan_and_master_data(): void
    {
        $admin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'super@ppsdmk.go.id',
            'password'  => Hash::make('secret123'),
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(KelolaAdmin::class)
            ->set('newKategoriNama', 'PELATIHAN KEPEMIMPINAN')
            ->call('addKategori')
            ->set('newMetodeNama', 'Blended Field')
            ->set('newMetodeWarna', '#0ea5e9')
            ->set('newMetodeTeks', '#ffffff')
            ->call('addMetode')
            ->call('openCreate')
            ->set('formTahun', 2026)
            ->set('kategori', 'PELATIHAN KEPEMIMPINAN')
            ->set('nama_pelatihan', 'PKN Tingkat II')
            ->set('angkatan', 'Angkatan V')
            ->set('jumlah_peserta', '35')
            ->set('peserta_terdaftar', '35')
            ->set('peserta_lulus', '34')
            ->set('jam_pelajaran', '300')
            ->set('tanggal_mulai', '2026-06-01')
            ->set('tanggal_selesai', '2026-08-30')
            ->set('seleksi_hari', 14)
            ->set('tahapanList', [
                ['tahap' => 'Mandiri/MOOC', 'mulai' => '2026-06-01', 'selesai' => '2026-06-15'],
                ['tahap' => 'Klasikal', 'mulai' => '2026-08-01', 'selesai' => '2026-08-30'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $saved = Pelatihan::where('nama_pelatihan', 'PKN Tingkat II')->first();
        $this->assertNotNull($saved);
        $this->assertSame(14, $saved->seleksi_hari);
        $this->assertCount(2, $saved->jadwal_tahapan);
        $this->assertTrue(MasterKategori::where('nama', 'PELATIHAN KEPEMIMPINAN')->exists());
        $this->assertTrue(MasterMetode::where('nama', 'Blended Field')->exists());
        $this->assertTrue(AuditLog::where('action', 'create')->exists());
    }

    public function test_viewer_cannot_modify_training_or_manage_users(): void
    {
        $viewer = User::create([
            'name'      => 'Pimpinan Viewer',
            'email'     => 'viewer@ppsdmk.go.id',
            'password'  => Hash::make('secret123'),
            'role'      => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $this->actingAs($viewer);

        Livewire::test(KelolaAdmin::class)
            ->call('openCreate')
            ->assertSet('showModal', false)
            ->assertSet('notifType', 'error');
    }

    public function test_excel_export_and_import_preserve_multi_tahap_and_rollback_on_error(): void
    {
        Pelatihan::create([
            'tahun'          => 2026,
            'kategori'       => 'PELATIHAN FUNGSIONAL',
            'nama_pelatihan' => 'Existing Training',
            'jumlah_peserta' => 20,
            'tanggal_mulai'  => '2026-04-01',
            'tanggal_selesai'=> '2026-04-05',
            'jadwal_tahapan' => [
                ['tahap' => 'Mandiri/MOOC', 'mulai' => '2026-04-01', 'selesai' => '2026-04-02'],
                ['tahap' => 'Klasikal', 'mulai' => '2026-04-03', 'selesai' => '2026-04-05'],
            ],
        ]);

        // Export to XLSX and re-import in replace mode
        $xlsxPath = PelatihanExportService::exportToXlsx(Pelatihan::all(), 2026);
        $this->assertFileExists($xlsxPath);

        $result = PelatihanImportService::import($xlsxPath, 'replace');
        @unlink($xlsxPath);

        $this->assertSame(1, $result['imported']);
        $reimported = Pelatihan::first();
        $this->assertSame('Existing Training', $reimported->nama_pelatihan);
        $this->assertCount(2, $reimported->jadwal_tahapan);

        // Invalid CSV in replace mode must throw and keep existing data intact
        $badCsv = tempnam(sys_get_temp_dir(), 'bad_') . '.csv';
        file_put_contents($badCsv, "Kategori,Nama Pelatihan,Tanggal Mulai\nFUNGSIONAL,Tanpa Tanggal,\n");

        try {
            PelatihanImportService::import($badCsv, 'replace');
            $this->fail('Expected exception for invalid CSV rows.');
        } catch (\Exception $e) {
            $this->assertSame(1, Pelatihan::count());
        } finally {
            @unlink($badCsv);
        }
    }

    public function test_database_seeder_and_template_download_work_end_to_end(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertGreaterThan(10, Pelatihan::count());
        $this->assertGreaterThan(0, MasterKategori::count());
        $this->assertGreaterThan(0, MasterMetode::count());
        $this->assertGreaterThan(0, HariLibur::count());

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('KALENDER PELATIHAN TAHUN 2026')
            ->assertSee('Berikutnya →');

        $admin = User::where('email', 'admin@pelatihan.com')->first();
        $this->assertNotNull($admin);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Panel Kelola Pelatihan PPSDMK')
            ->assertSee('Berikutnya →');

        $this->get(route('admin.template.kalender.download', ['tahun' => 2026]))
            ->assertStatus(200);

        // Generated user-friendly template should preview and re-import cleanly
        $templatePath = PelatihanExportService::generateTemplateXlsx(2026);
        $preview      = PelatihanImportService::preview($templatePath);
        $this->assertSame(3, $preview['valid_count']);
        $this->assertSame(0, $preview['skipped']);

        $imported = PelatihanImportService::import($templatePath, 'replace');
        @unlink($templatePath);
        $this->assertSame(3, $imported['imported']);
    }

    public function test_planning_draft_publish_clone_year_and_public_calendar_toggle(): void
    {
        $admin = User::create([
            'name'      => 'Admin Planner',
            'email'     => 'planner@ppsdmk.go.id',
            'password'  => Hash::make('secret123'),
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        HariLibur::create([
            'tahun'           => 2027,
            'nama'            => 'Cuti Bersama',
            'tanggal_mulai'   => '2027-06-10',
            'tanggal_selesai' => '2027-06-12',
            'is_active'       => true,
        ]);

        $p2026 = Pelatihan::create([
            'tahun'           => 2026,
            'kategori'        => 'PELATIHAN FUNGSIONAL',
            'nama_pelatihan'  => 'JF Pengawas Tahunan',
            'jumlah_peserta'  => 30,
            'peserta_lulus'   => 29,
            'tanggal_mulai'   => '2026-06-05',
            'tanggal_selesai' => '2026-06-15',
            'is_published'    => true,
        ]);

        $this->actingAs($admin);

        // Clone 2026 -> 2027 creates Draft (+1 year dates, reset lulus = 0)
        Livewire::test(KelolaAdmin::class)
            ->set('tahun', 2027)
            ->call('cloneFromPreviousYear')
            ->assertSet('notifType', 'success');

        $cloned = Pelatihan::where('tahun', 2027)->first();
        $this->assertNotNull($cloned);
        $this->assertFalse($cloned->is_published);
        $this->assertSame(0, $cloned->peserta_lulus);
        $this->assertSame('2027-06-05', $cloned->tanggal_mulai->format('Y-m-d'));
        $this->assertNotEmpty($cloned->getHolidayConflicts(HariLibur::where('tahun', 2027)->get()));

        // Draft item must NOT appear on public dashboard until published
        Livewire::test(DaftarPelatihan::class)
            ->set('tahun', 2027)
            ->assertDontSee('JF Pengawas Tahunan');

        // Publish all 2027 & verify it becomes visible
        Livewire::test(KelolaAdmin::class)
            ->set('tahun', 2027)
            ->call('publishAllYear');

        Livewire::test(DaftarPelatihan::class)
            ->set('tahun', 2027)
            ->assertSee('JF Pengawas Tahunan')
            ->assertSee('KALENDER PELATIHAN TAHUN 2027');

        // Toggle public calendar visibility OFF -> public calendar hidden, schedule still visible
        Livewire::test(KelolaAdmin::class)
            ->call('togglePublicKalender');

        Livewire::test(DaftarPelatihan::class)
            ->set('tahun', 2027)
            ->assertSee('JF Pengawas Tahunan')
            ->assertDontSee('KALENDER PELATIHAN TAHUN 2027');
    }
}
