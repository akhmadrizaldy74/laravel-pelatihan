<?php

namespace App\Services;

use App\Models\MasterKategori;
use App\Models\MasterMetode;
use App\Models\Pelatihan;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PelatihanExportService
{
    /**
     * Export trainings collection to a formatted Excel (.xlsx) file and return temp path.
     */
    public static function exportToXlsx(Collection $pelatihans, int $tahun): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Pelatihan ' . $tahun);

        // Title Banner
        $sheet->setCellValue('A1', "REKAPITULASI JADWAL & KALENDER PELATIHAN PPSDMK TAHUN {$tahun}");
        $sheet->mergeCells('A1:S1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0D47A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setCellValue('A2', 'Kemnaker Corporate University — Diekspor pada: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:S2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
        ]);

        // Headers
        $headers = [
            'No',
            'Kode',
            'Kategori',
            'Nama Pelatihan',
            'Angkatan',
            'Target Peserta',
            'Terdaftar',
            'Lulus',
            'JP',
            'Durasi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Sumber Dana',
            'Tempat',
            'Metode Utama',
            'Penyelenggara',
            'PIC',
            'Status',
            'Jadwal Tahapan (Multi-Tahap)',
        ];

        $headerRow = 4;
        foreach ($headers as $idx => $title) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $sheet->getStyle("A{$headerRow}:S{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        $rowNum = $headerRow + 1;
        foreach ($pelatihans->values() as $index => $p) {
            $tahapanStr = '';
            if (!empty($p->jadwal_tahapan) && is_array($p->jadwal_tahapan)) {
                $parts = [];
                foreach ($p->jadwal_tahapan as $t) {
                    $label = $t['tahap'] ?? $t['metode'] ?? 'Tahap';
                    $m     = $t['mulai'] ?? '';
                    $s     = $t['selesai'] ?? '';
                    $parts[] = "{$label}: {$m} s/d {$s}";
                }
                $tahapanStr = implode('; ', $parts);
            }

            $values = [
                $index + 1,
                $p->kode_pelatihan ?: '-',
                $p->kategori ?: 'PELATIHAN FUNGSIONAL',
                $p->nama_pelatihan,
                $p->angkatan ?: '-',
                (int)$p->jumlah_peserta,
                (int)$p->peserta_terdaftar,
                (int)$p->peserta_lulus,
                $p->jam_pelajaran ?: '-',
                $p->durasi_display,
                $p->tanggal_mulai ? $p->tanggal_mulai->format('Y-m-d') : '-',
                $p->tanggal_selesai ? $p->tanggal_selesai->format('Y-m-d') : '-',
                $p->sumber_dana ?: 'RM',
                $p->tempat_pelaksanaan ?: 'PPSDM',
                $p->metode ?: 'Klasikal',
                $p->penyelenggara ?: '-',
                $p->pic_nama ? ($p->pic_nama . ($p->pic_kontak ? " ({$p->pic_kontak})" : '')) : '-',
                $p->computed_status,
                $tahapanStr ?: '-',
            ];

            foreach ($values as $cIdx => $val) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                $sheet->setCellValue($col . $rowNum, $val);
            }

            $bg = ($index % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
            $sheet->getStyle("A{$rowNum}:S{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'font'    => ['size' => 10],
            ]);

            $rowNum++;
        }

        // Totals Row
        if ($pelatihans->isNotEmpty()) {
            $sheet->setCellValue("A{$rowNum}", 'TOTAL');
            $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
            $sheet->setCellValue("F{$rowNum}", $pelatihans->sum('jumlah_peserta'));
            $sheet->setCellValue("G{$rowNum}", $pelatihans->sum('peserta_terdaftar'));
            $sheet->setCellValue("H{$rowNum}", $pelatihans->sum('peserta_lulus'));
            $sheet->getStyle("A{$rowNum}:S{$rowNum}")->applyFromArray([
                'font'    => ['bold' => true, 'size' => 10],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
            ]);
        }

        foreach (range('A', 'S') as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Sheet 2: Ringkasan Eksekutif
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Ringkasan Eksekutif');
        $summarySheet->setCellValue('A1', "RINGKASAN EKSEKUTIF PELATIHAN {$tahun}");
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $summarySheet->setCellValue('A3', 'Kategori');
        $summarySheet->setCellValue('B3', 'Jumlah Pelatihan');
        $summarySheet->setCellValue('C3', 'Target Peserta');
        $summarySheet->setCellValue('D3', 'Terdaftar');
        $summarySheet->setCellValue('E3', 'Realisasi Lulus');
        $summarySheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
        ]);

        $sRow = 4;
        foreach ($pelatihans->groupBy(fn($p) => $p->kategori ?: 'LAINNYA') as $cat => $items) {
            $summarySheet->setCellValue("A{$sRow}", $cat);
            $summarySheet->setCellValue("B{$sRow}", $items->count());
            $summarySheet->setCellValue("C{$sRow}", $items->sum('jumlah_peserta'));
            $summarySheet->setCellValue("D{$sRow}", $items->sum('peserta_terdaftar'));
            $summarySheet->setCellValue("E{$sRow}", $items->sum('peserta_lulus'));
            $sRow++;
        }

        foreach (range('A', 'E') as $colLetter) {
            $summarySheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'ppsdmk_export_') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Dynamically generate a user-friendly Excel import template with hints,
     * dropdown data validations, realistic PPSDMK examples, and a guide sheet.
     */
    public static function generateTemplateXlsx(?int $tahun = null): string
    {
        $year        = $tahun ?: (int)now()->year;
        $spreadsheet = new Spreadsheet();

        $kategoris = MasterKategori::getActiveNames();
        $metodes   = MasterMetode::getActiveCached()->pluck('nama')->all();
        if (empty($metodes)) {
            $metodes = ['Online/Daring', 'Klasikal', 'Mandiri/MOOC', 'PKL + Bimbingan', 'Seminar + Penutupan', 'Assessment'];
        }

        // ============================================================
        // SHEET 1: ISI_DATA_PELATIHAN
        // ============================================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ISI_DATA_PELATIHAN');

        // Row 1: Title Banner
        $sheet->setCellValue('A1', "TEMPLATE JADWAL & KALENDER PELATIHAN PPSDMK TAHUN {$year} — CUKUP ISI MULAI BARIS 4 (BARIS CONTOH BOLEH DITIMPA/DIHAPUS)");
        $sheet->mergeCells('A1:U1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D47A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2: Column-by-column hints
        $hints = [
            '[Wajib] Pilih dropdown',
            '[Wajib] Nama pelatihan lengkap',
            '[Wajib] Angka kuota (mis: 30)',
            '[Wajib] YYYY-MM-DD (mis: 2026-09-14)',
            '[Wajib] YYYY-MM-DD (mis: 2026-11-11)',
            '[Opsional] Pilih metode utama',
            '[Opsional] RM / SWA / PNBP',
            '[Opsional] PPSDM / LUAR',
            '[Opsional] Otomatis jika kosong',
            '[Opsional] Mis: Angkatan 111',
            '[Opsional] Mis: JF-PK-111',
            '[Opsional] Total Jam Pelajaran',
            '[Opsional] Angka pendaftar',
            '[Opsional] Angka lulus',
            '[Opsional] Bidang/Unit pengusul',
            '[Opsional] Nama narahubung',
            '[Opsional] No. HP / Ext',
            '[Opsional] Default: 7 hari',
            '[Opsional] Tahap: TglMulai s/d TglSelesai; ...',
            '[Opsional] URL LMS / Pendaftaran',
            '[Opsional] Keterangan / syarat peserta',
        ];

        foreach ($hints as $idx => $hintText) {
            $col = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($col . '2', $hintText);
        }
        $sheet->getStyle('A2:U2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Row 3: Table Headers (Required = A-E, Optional = F-U)
        $headers = [
            'Kategori *',
            'Nama Pelatihan *',
            'Target Peserta *',
            'Tanggal Mulai *',
            'Tanggal Selesai *',
            'Metode Utama',
            'Sumber Dana',
            'Tempat',
            'Durasi',
            'Angkatan',
            'Kode',
            'JP',
            'Terdaftar',
            'Lulus',
            'Penyelenggara',
            'PIC',
            'Kontak PIC',
            'Seleksi (H-)',
            'Jadwal Tahapan',
            'Link Pendaftaran',
            'Deskripsi',
        ];

        foreach ($headers as $idx => $h) {
            $col = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($col . '3', $h);
        }

        // Required headers A3:E3 in strong blue
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        // Optional headers F3:U3 in dark slate
        $sheet->getStyle('F3:U3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(26);

        // Rows 4-6: Realistic PPSDMK examples
        $samples = [
            [
                $kategoris[0] ?? 'PELATIHAN FUNGSIONAL',
                'Pelat. Pembentukan JF Pengawas Ketenagakerjaan',
                30,
                "{$year}-09-14",
                "{$year}-11-11",
                'Klasikal',
                'RM',
                'PPSDM',
                '26 hr',
                'Angkatan 111',
                'JF-PK-111',
                210,
                28,
                0,
                'Bidang Pelatihan Fungsional',
                'Budi Santoso',
                '081234567890',
                14,
                "Mandiri/MOOC: {$year}-09-14 s/d {$year}-09-28; Online/Daring: {$year}-10-07 s/d {$year}-10-26; Klasikal: {$year}-10-28 s/d {$year}-11-11",
                'https://corpu.kemnaker.go.id',
                'Contoh multi-tahap (hapus atau ganti dengan data asli Anda).',
            ],
            [
                $kategoris[1] ?? 'PELATIHAN MANAJERIAL',
                'Pelatihan Kepemimpinan Administrator (PKA)',
                30,
                "{$year}-04-13",
                "{$year}-08-28",
                'Klasikal',
                'RM',
                'PPSDM',
                '64 hr',
                'Angkatan I',
                'PKA-01',
                450,
                30,
                30,
                'Bidang Pelatihan Manajerial',
                'Siti Rahma',
                '081398765432',
                14,
                "Mandiri/MOOC: {$year}-04-13 s/d {$year}-04-27; Klasikal: {$year}-06-02 s/d {$year}-06-22",
                '',
                'Contoh pelatihan manajerial.',
            ],
            [
                $kategoris[2] ?? 'PELATIHAN TEKNIS',
                'Pelatihan Pengelolaan Arsip Elektronik',
                30,
                "{$year}-05-04",
                "{$year}-05-09",
                'Online/Daring',
                'PNBP',
                'PPSDM',
                '6 hr',
                'Gelombang I',
                'TEK-AE-01',
                48,
                30,
                0,
                'Bidang Pelatihan Teknis',
                '',
                '',
                7,
                '',
                '',
                'Contoh pelatihan 1 tahap (Jadwal Tahapan dikosongkan).',
            ],
        ];

        $rowNum = 4;
        foreach ($samples as $sampleRow) {
            foreach ($sampleRow as $cIdx => $val) {
                $col = Coordinate::stringFromColumnIndex($cIdx + 1);
                $sheet->setCellValue($col . $rowNum, $val);
            }
            $sheet->getStyle("A{$rowNum}:U{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEFCE8']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                'font'    => ['size' => 10],
            ]);
            $rowNum++;
        }

        // Format date columns D & E as YYYY-MM-DD for rows 4..200
        $sheet->getStyle('D4:E200')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->freezePane('C4');

        // Column widths
        $widths = [
            'A' => 26, 'B' => 42, 'C' => 16, 'D' => 18, 'E' => 18,
            'F' => 22, 'G' => 14, 'H' => 14, 'I' => 14, 'J' => 16,
            'K' => 14, 'L' => 10, 'M' => 12, 'N' => 10, 'O' => 26,
            'P' => 18, 'Q' => 16, 'R' => 14, 'S' => 54, 'T' => 28, 'U' => 36,
        ];
        foreach ($widths as $colLetter => $w) {
            $sheet->getColumnDimension($colLetter)->setWidth($w);
        }

        // ============================================================
        // SHEET 2: PANDUAN_PENGISIAN
        // ============================================================
        $guideSheet = $spreadsheet->createSheet();
        $guideSheet->setTitle('PANDUAN_PENGISIAN');
        $guideRows = [
            ['PANDUAN PENGISIAN TEMPLATE EXCEL PELATIHAN PPSDMK', ''],
            ['1. Kolom Wajib (*)', 'Cukup isi 5 kolom berwarna biru (Kategori *, Nama Pelatihan *, Target Peserta *, Tanggal Mulai *, Tanggal Selesai *). Kolom lain boleh dikosongkan.'],
            ['2. Format Tanggal', 'Gunakan format YYYY-MM-DD (contoh: 2026-09-14) atau tanggal Excel standar.'],
            ['3. Cara Mengisi Jadwal Tahapan (Opsional)', 'Jika pelatihan memiliki beberapa tahap untuk Gantt Chart, isi kolom Jadwal Tahapan dengan format:'],
            ['   Contoh Multi-Tahap', "Mandiri/MOOC: {$year}-09-14 s/d {$year}-09-28; Online/Daring: {$year}-10-07 s/d {$year}-10-26; Klasikal: {$year}-10-28 s/d {$year}-11-11"],
            ['   Pemisah Antar Tahap', 'Gunakan titik koma (;) antar tahapan. Jika dikosongkan, kalender memakai 1 batang sesuai Metode Utama & Tanggal Mulai–Selesai.'],
            ['4. Cara Upload di Admin Panel', 'Buka Panel Admin -> klik Upload Excel -> pilih file ini -> periksa kotak preview -> pilih mode (Ganti Total atau Tambahkan) -> klik Proses & Import.'],
        ];
        foreach ($guideRows as $rIdx => $gRow) {
            $r = $rIdx + 1;
            $guideSheet->setCellValue("A{$r}", $gRow[0]);
            $guideSheet->setCellValue("B{$r}", $gRow[1]);
        }
        $guideSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '0D47A1']],
        ]);
        $guideSheet->getStyle('A2:A7')->getFont()->setBold(true);
        $guideSheet->getColumnDimension('A')->setWidth(38);
        $guideSheet->getColumnDimension('B')->setWidth(95);

        // ============================================================
        // SHEET 3: REFERENSI_MASTER (untuk Dropdown Validation)
        // ============================================================
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('REFERENSI_MASTER');
        $refSheet->setCellValue('A1', 'DAFTAR_KATEGORI');
        $refSheet->setCellValue('B1', 'DAFTAR_METODE');
        $refSheet->getStyle('A1:B1')->getFont()->setBold(true);

        foreach ($kategoris as $idx => $kat) {
            $refSheet->setCellValue('A' . ($idx + 2), $kat);
        }
        foreach ($metodes as $idx => $met) {
            $refSheet->setCellValue('B' . ($idx + 2), $met);
        }
        $refSheet->getColumnDimension('A')->setWidth(32);
        $refSheet->getColumnDimension('B')->setWidth(34);

        // Apply Excel Dropdown Validation on Sheet 1 rows 4..200
        $katMaxRow = max(2, count($kategoris) + 1);
        $metMaxRow = max(2, count($metodes) + 1);

        $katValidation = $sheet->getCell('A4')->getDataValidation();
        $katValidation->setType(DataValidation::TYPE_LIST);
        $katValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $katValidation->setAllowBlank(true);
        $katValidation->setShowDropDown(true);
        $katValidation->setFormula1("=REFERENSI_MASTER!\$A\$2:\$A\${$katMaxRow}");

        $metValidation = $sheet->getCell('F4')->getDataValidation();
        $metValidation->setType(DataValidation::TYPE_LIST);
        $metValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $metValidation->setAllowBlank(true);
        $metValidation->setShowDropDown(true);
        $metValidation->setFormula1("=REFERENSI_MASTER!\$B\$2:\$B\${$metMaxRow}");

        for ($r = 4; $r <= 200; $r++) {
            $sheet->getCell("A{$r}")->setDataValidation(clone $katValidation);
            $sheet->getCell("F{$r}")->setDataValidation(clone $metValidation);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'ppsdmk_template_') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
