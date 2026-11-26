<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\Pelatihan;
use App\Services\PelatihanExportService;
use App\Services\PelatihanImportService;

trait ManagesExcelImportExport
{
    public function openImportModal(): void
    {
        if (!$this->canManage()) return;
        $this->fileExcel       = null;
        $this->importErrors    = [];
        $this->importPreview   = null;
        $this->importAsDraft   = false;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->fileExcel       = null;
        $this->importErrors    = [];
        $this->importPreview   = null;
        $this->importAsDraft   = false;
    }

    public function updatedFileExcel(): void
    {
        $this->importErrors  = [];
        $this->importPreview = null;

        if (!$this->fileExcel) {
            return;
        }

        try {
            $this->validate([
                'fileExcel' => 'file|mimes:xlsx,xls,csv|max:10240',
            ], [
                'fileExcel.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
                'fileExcel.max'   => 'Ukuran file maksimal 10MB.',
            ]);

            $this->importPreview = PelatihanImportService::preview($this->fileExcel->getRealPath());
        } catch (\Throwable $e) {
            $this->importErrors = [$e->getMessage()];
        }
    }

    public function importExcel(): void
    {
        if (!$this->canManage()) return;

        $this->validate([
            'fileExcel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'fileExcel.required' => 'Pilih file Excel (.xlsx / .xls) atau CSV terlebih dahulu.',
            'fileExcel.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'fileExcel.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $realPath = $this->fileExcel->getRealPath();
            $result   = PelatihanImportService::import($realPath, $this->importMode, $this->importAsDraft);

            $this->showImportModal = false;
            $this->fileExcel       = null;
            $this->importPreview   = null;
            $this->importWarnings  = $result['errors'] ?? [];

            AuditLog::record(
                'import',
                "Import Excel ({$this->importMode}): {$result['imported']} masuk, {$result['skipped']} dilewati.",
                'Pelatihan'
            );

            $skipNote = ($result['skipped'] > 0) ? " ({$result['skipped']} baris dilewati)." : '.';
            $this->flashSuccess("Berhasil memproses Excel! {$result['imported']} data pelatihan berhasil masuk{$skipNote}");
        } catch (\Exception $e) {
            $this->importErrors = [$e->getMessage()];
            $this->flashError('Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function clearImportWarnings(): void
    {
        $this->importWarnings = [];
    }

    public function exportExcel()
    {
        $filtered = $this->getFilteredCollection(
            Pelatihan::forYear($this->tahun)->orderBy('tanggal_mulai')->get()
        );

        AuditLog::record('export', "Export Excel jadwal tahun {$this->tahun} ({$filtered->count()} baris).", 'Pelatihan');

        $path     = PelatihanExportService::exportToXlsx($filtered, $this->tahun);
        $filename = "Laporan_Pelatihan_PPSDMK_{$this->tahun}.xlsx";

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
