<?php

use App\Livewire\Admin\KelolaAdmin;
use App\Livewire\Admin\LoginAdmin;
use App\Models\HariLibur;
use App\Models\MasterMetode;
use App\Models\Pelatihan;
use App\Services\PelatihanExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public User Dashboard
Route::get('/', function () {
    return view('dashboard');
})->name('public.dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
});

// Public Print / Save as PDF Calendar View
Route::get('/kalender/cetak', function (Request $request) {
    $tahun = (int)($request->query('tahun') ?: now()->year);
    $pelatihans = Pelatihan::published()->forYear($tahun)->orderBy('tanggal_mulai')->get();
    $kalenderGroups = $pelatihans->sortBy('id')->groupBy(fn($i) => strtoupper($i->kategori ?: 'PELATIHAN FUNGSIONAL'));
    $hariLiburs = HariLibur::where('tahun', $tahun)->where('is_active', true)->orderBy('tanggal_mulai')->get();
    $masterMetodes = MasterMetode::getActiveCached();

    return view('print-kalender', compact('tahun', 'pelatihans', 'kalenderGroups', 'hariLiburs', 'masterMetodes'));
})->name('public.kalender.print');

// Admin Authentication Routes
Route::get('/admin/login', LoginAdmin::class)->name('admin.login');

Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout');

// Download Template Excel Jadwal Biasa
Route::get('/download-template-excel', function () {
    $path = public_path('templates' . DIRECTORY_SEPARATOR . 'template_jadwal_pelatihan.xlsx');
    if (!file_exists($path)) {
        $generated = PelatihanExportService::generateTemplateXlsx();
        return response()->download($generated, 'Template_Jadwal_Pelatihan_' . now()->year . '.xlsx')->deleteFileAfterSend(true);
    }
    return response()->download($path, 'Template_Jadwal_Pelatihan_' . now()->year . '.xlsx', [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
})->name('admin.template.download');

// Download Template Excel Kalender Pelatihan (Dinamis + Multi-Tahap)
Route::get('/download-template-kalender', function (Request $request) {
    $tahun = (int)($request->query('tahun') ?: now()->year);
    $generated = PelatihanExportService::generateTemplateXlsx($tahun);
    return response()->download($generated, "Template_Kalender_Pelatihan_{$tahun}.xlsx", [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend(true);
})->name('admin.template.kalender.download');

// Protected Admin Panel
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/', KelolaAdmin::class)->name('admin.dashboard');
    Route::get('/pelatihan', KelolaAdmin::class)->name('admin.pelatihan');
});
