<?php

namespace App\Services;

use App\Models\MasterKategori;
use App\Models\Pelatihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PelatihanImportService
{
    /**
     * Parse and validate Excel/CSV file without writing to DB (for live preview).
     */
    public static function preview(string $filePath): array
    {
        $parsed = self::parseFile($filePath);

        return [
            'valid_count' => count($parsed['records']),
            'skipped'     => $parsed['skipped'],
            'errors'      => $parsed['errors'],
            'sample'      => array_slice($parsed['records'], 0, 5),
        ];
    }

    /**
     * Import training schedules / calendar from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param string $filePath Absolute path to the uploaded file
     * @param string $mode 'replace' to clear existing or 'append' to add to existing
     * @return array Summary of import results
     */
    public static function import(string $filePath, string $mode = 'append', bool $asDraft = false): array
    {
        $parsed         = self::parseFile($filePath, !$asDraft);
        $records        = $parsed['records'];
        $skipped        = $parsed['skipped'];
        $errors         = $parsed['errors'];
        $discoveredCats = $parsed['discoveredCats'];

        if (empty($records)) {
            $detailMsg = !empty($errors) ? ' Detail: ' . implode(' | ', array_slice($errors, 0, 3)) : '';
            throw new \Exception('Tidak ada data pelatihan yang valid di dalam file. Pastikan ada nama pelatihan dan tanggal.' . $detailMsg);
        }

        // Use DB transaction with delete() instead of truncate() so failures roll back safely
        DB::transaction(function () use ($mode, $records, $discoveredCats) {
            if ($mode === 'replace') {
                Pelatihan::query()->delete();
            } elseif ($mode === 'replace_year') {
                $yearsInFile = collect($records)->pluck('tahun')->unique()->filter()->values()->all();
                if (!empty($yearsInFile)) {
                    Pelatihan::whereIn('tahun', $yearsInFile)->delete();
                }
            }

            foreach (array_chunk($records, 50) as $chunk) {
                Pelatihan::insert($chunk);
            }

            $maxOrder = (int)MasterKategori::max('urutan');
            foreach (array_keys($discoveredCats) as $catName) {
                if (!MasterKategori::where('nama', $catName)->exists()) {
                    $maxOrder++;
                    MasterKategori::create([
                        'nama'      => $catName,
                        'urutan'    => $maxOrder,
                        'is_active' => true,
                    ]);
                }
            }
        });

        return [
            'success'  => true,
            'imported' => count($records),
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    /**
     * Parse spreadsheet/CSV rows into normalized records and diagnostics.
     */
    protected static function parseFile(string $filePath, bool $isPublished = true): array
    {
        $rows = self::readRows($filePath);

        if (empty($rows)) {
            throw new \Exception('File kosong atau tidak dapat dibaca. Pastikan format file sesuai (.xlsx, .xls, .csv).');
        }

        $headerIndex = -1;
        $columnMap   = [];

        foreach ($rows as $index => $row) {
            $mapped = self::detectHeader($row);
            if ($mapped !== null) {
                $headerIndex = $index;
                $columnMap   = $mapped;
                break;
            }
        }

        if ($headerIndex === -1) {
            $columnMap      = self::fallbackColumnMap($rows[0] ?? []);
            $dataStartIndex = (isset($rows[0][0]) && is_string($rows[0][0]) && !is_numeric($rows[0][0])) ? 1 : 0;
        } else {
            $dataStartIndex = $headerIndex + 1;
        }

        $records         = [];
        $skipped         = 0;
        $errors          = [];
        $discoveredCats  = [];
        $currentKategori = 'PELATIHAN FUNGSIONAL';

        for ($i = $dataStartIndex; $i < count($rows); $i++) {
            $row = $rows[$i];

            $hasContent = false;
            foreach ($row as $cell) {
                if (trim((string)$cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) {
                continue;
            }

            $nonEmptyCount = count(array_filter($row, fn($c) => trim((string)$c) !== ''));
            $firstCell     = trim((string)($row[0] ?? $row[1] ?? ''));
            if (
                preg_match('/^(\[?(wajib|opsional|petunjuk|contoh)\]?)/iu', $firstCell) ||
                preg_match('/^template\s+jadwal/iu', $firstCell)
            ) {
                continue;
            }

            if (
                $nonEmptyCount === 1 && (
                    preg_match('/^(\x{25BC}|\x{25B6}|▼|▶|-)?\s*(pelatihan\s+(fungsional|manajerial|teknis|kepemimpinan|sosial))/iu', $firstCell) ||
                    (strlen($firstCell) > 5 && !preg_match('/[0-9]/', $firstCell))
                )
            ) {
                $cleanCat = trim(preg_replace('/^(\x{25BC}|\x{25B6}|▼|▶|-|\*)\s*/u', '', $firstCell));
                if (!empty($cleanCat) && strlen($cleanCat) < 80) {
                    $currentKategori = strtoupper($cleanCat);
                    $discoveredCats[$currentKategori] = true;
                    continue;
                }
            }

            $namaCol = $columnMap['nama'] ?? 1;
            $nama    = trim((string)($row[$namaCol] ?? ''));

            if (
                $nama === '' ||
                in_array(strtolower(str_replace('*', '', $nama)), ['nama pelatihan', 'jenis pelatihan', 'nama'], true) ||
                preg_match('/^(\[?(wajib|opsional|petunjuk)\]?)/iu', $nama)
            ) {
                continue;
            }

            $kategori = $currentKategori;
            if (isset($columnMap['kategori']) && !empty(trim((string)($row[$columnMap['kategori']] ?? '')))) {
                $kategori = strtoupper(trim((string)$row[$columnMap['kategori']]));
            }
            $discoveredCats[$kategori] = true;

            $pesertaCol = $columnMap['peserta'] ?? 2;
            $pesertaRaw = $row[$pesertaCol] ?? 30;
            $peserta    = (int)preg_replace('/[^0-9]/', '', (string)$pesertaRaw);
            if ($peserta <= 0) {
                $peserta = 30;
            }

            $terdaftar = isset($columnMap['terdaftar'])
                ? (int)preg_replace('/[^0-9]/', '', (string)($row[$columnMap['terdaftar']] ?? 0))
                : 0;

            $lulus = isset($columnMap['lulus'])
                ? (int)preg_replace('/[^0-9]/', '', (string)($row[$columnMap['lulus']] ?? 0))
                : 0;

            $durasi = null;
            if (isset($columnMap['durasi'])) {
                $durasiRaw = trim((string)($row[$columnMap['durasi']] ?? ''));
                if (!empty($durasiRaw)) {
                    $durasi = $durasiRaw;
                }
            }

            $jp = null;
            if (isset($columnMap['jp'])) {
                $jpRaw = (int)preg_replace('/[^0-9]/', '', (string)($row[$columnMap['jp']] ?? ''));
                if ($jpRaw > 0) {
                    $jp = $jpRaw;
                }
            }

            $sumberDana = 'RM';
            if (isset($columnMap['sumber_dana'])) {
                $sdRaw = trim((string)($row[$columnMap['sumber_dana']] ?? ''));
                if (!empty($sdRaw)) {
                    $sumberDana = strtoupper($sdRaw);
                }
            }

            $tempat = 'PPSDM';
            if (isset($columnMap['tempat'])) {
                $tpRaw = trim((string)($row[$columnMap['tempat']] ?? ''));
                if (!empty($tpRaw)) {
                    $tempat = strtoupper($tpRaw);
                }
            }

            $metode = 'Klasikal';
            if (isset($columnMap['metode'])) {
                $mtRaw = trim((string)($row[$columnMap['metode']] ?? ''));
                if (!empty($mtRaw)) {
                    $metode = $mtRaw;
                }
            }

            $mulaiCol   = $columnMap['mulai'] ?? 3;
            $selesaiCol = $columnMap['selesai'] ?? 4;

            $mulai   = self::parseDate($row[$mulaiCol] ?? null);
            $selesai = self::parseDate($row[$selesaiCol] ?? null);

            $tahapan = null;
            if (isset($columnMap['tahapan'])) {
                $tahapanRaw = trim((string)($row[$columnMap['tahapan']] ?? ''));
                if ($tahapanRaw !== '') {
                    $tahapan = self::parseTahapan($tahapanRaw, $mulai?->year);
                }
            }

            if (!$mulai && !empty($tahapan)) {
                $mulai   = Carbon::parse($tahapan[0]['mulai']);
                $selesai = Carbon::parse($tahapan[count($tahapan) - 1]['selesai']);
            }

            if (!$mulai) {
                $skipped++;
                $errors[] = "Baris " . ($i + 1) . " ('$nama'): Format Tanggal Mulai tidak valid atau kosong.";
                continue;
            }

            if (!$selesai) {
                $selesai = $mulai->copy()->addDays(5);
            }

            if ($selesai->lt($mulai)) {
                [$mulai, $selesai] = [$selesai, $mulai];
            }

            if (!$durasi) {
                $days   = $mulai->diffInDays($selesai) + 1;
                $durasi = $days . ' hr';
            }

            $tahun = (int)$mulai->year;
            if (isset($columnMap['tahun'])) {
                $tahunRaw = (int)preg_replace('/[^0-9]/', '', (string)($row[$columnMap['tahun']] ?? ''));
                if ($tahunRaw >= 2000 && $tahunRaw <= 2100) {
                    $tahun = $tahunRaw;
                }
            }

            $seleksiHari = 7;
            if (isset($columnMap['seleksi_hari'])) {
                $shRaw = trim((string)($row[$columnMap['seleksi_hari']] ?? ''));
                if ($shRaw !== '') {
                    $seleksiHari = max(0, (int)preg_replace('/[^0-9]/', '', $shRaw));
                }
            }

            $status = self::computeStatus($mulai, $selesai, $seleksiHari);

            $records[] = [
                'tahun'              => $tahun,
                'kategori'           => $kategori,
                'kode_pelatihan'     => isset($columnMap['kode']) ? (trim((string)($row[$columnMap['kode']] ?? '')) ?: null) : null,
                'nama_pelatihan'     => $nama,
                'angkatan'           => isset($columnMap['angkatan']) ? (trim((string)($row[$columnMap['angkatan']] ?? '')) ?: null) : null,
                'jumlah_peserta'     => $peserta,
                'peserta_terdaftar'  => $terdaftar,
                'peserta_lulus'      => $lulus,
                'durasi'             => $durasi,
                'jam_pelajaran'      => $jp,
                'tanggal_mulai'      => $mulai->format('Y-m-d'),
                'tanggal_selesai'    => $selesai->format('Y-m-d'),
                'sumber_dana'        => $sumberDana,
                'tempat_pelaksanaan' => $tempat,
                'penyelenggara'      => isset($columnMap['penyelenggara']) ? (trim((string)($row[$columnMap['penyelenggara']] ?? '')) ?: null) : null,
                'pic_nama'           => isset($columnMap['pic_nama']) ? (trim((string)($row[$columnMap['pic_nama']] ?? '')) ?: null) : null,
                'pic_kontak'         => isset($columnMap['pic_kontak']) ? (trim((string)($row[$columnMap['pic_kontak']] ?? '')) ?: null) : null,
                'link_pendaftaran'   => isset($columnMap['link']) ? (trim((string)($row[$columnMap['link']] ?? '')) ?: null) : null,
                'deskripsi'          => isset($columnMap['deskripsi']) ? (trim((string)($row[$columnMap['deskripsi']] ?? '')) ?: null) : null,
                'metode'             => $metode,
                'jadwal_tahapan'     => $tahapan !== null ? json_encode($tahapan) : null,
                'status'             => $status,
                'auto_status'        => true,
                'is_published'       => $isPublished,
                'seleksi_hari'       => $seleksiHari,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        return [
            'records'        => $records,
            'skipped'        => $skipped,
            'errors'         => $errors,
            'discoveredCats' => $discoveredCats,
        ];
    }

    /**
     * Parse multi-stage string such as:
     * "Mandiri/MOOC: 2026-09-14 s/d 2026-09-28; Online/Daring: 2026-10-07 s/d 2026-10-26"
     */
    public static function parseTahapan(string $raw, ?int $defaultYear = null): ?array
    {
        $segments = preg_split('/[;\|\r\n]+/', $raw);
        $stages   = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '' || !str_contains($segment, ':')) {
                continue;
            }

            [$stageName, $datePart] = explode(':', $segment, 2);
            $stageName = trim($stageName);
            $datePart  = trim($datePart);

            if ($stageName === '' || $datePart === '') {
                continue;
            }

            $parts = preg_split('/\s+(?:s\/d|sd|sampai|to)\s+|\s+-\s+/i', $datePart, 2);
            if (count($parts) === 1) {
                $parts = [$parts[0], $parts[0]];
            }

            $start = self::parseDate(trim($parts[0]), $defaultYear);
            $end   = self::parseDate(trim($parts[1]), $defaultYear);

            if ($start && $end) {
                if ($end->lt($start)) {
                    [$start, $end] = [$end, $start];
                }
                $stages[] = [
                    'tahap'   => $stageName,
                    'mulai'   => $start->format('Y-m-d'),
                    'selesai' => $end->format('Y-m-d'),
                ];
            }
        }

        return !empty($stages) ? $stages : null;
    }

    /**
     * Read all rows from file (Spreadsheet or CSV).
     */
    protected static function readRows(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($ext, ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            return $sheet->toArray(null, true, false, false);
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $firstLine = fgets($handle) ?: '';
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") {
                fread($handle, 3);
            }
            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

            while (($data = fgetcsv($handle, 10000, $delimiter)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }

    /**
     * Detect header column indexes by matching header names.
     */
    protected static function detectHeader(array $row): ?array
    {
        $map = [];

        foreach ($row as $colIndex => $cellValue) {
            $raw = trim((string)$cellValue);
            if ($raw === '' || preg_match('/^\[?(wajib|opsional|petunjuk|contoh)\]?/i', $raw) || strlen($raw) > 50) {
                continue;
            }
            $val = strtolower(trim(str_replace('*', '', $raw)));
            if ($val === '') {
                continue;
            }

            if (preg_match('/^(tahun|year)$/i', $val)) {
                $map['tahun'] = $colIndex;
            } elseif (preg_match('/^(kode|kode.*pelatihan|code)$/i', $val)) {
                $map['kode'] = $colIndex;
            } elseif (preg_match('/^(kategori|kelompok|grup|category)$/i', $val)) {
                $map['kategori'] = $colIndex;
            } elseif (preg_match('/(nama|jenis|judul|program|training).*pelatihan/i', $val) ||
                in_array($val, ['nama pelatihan', 'jenis pelatihan', 'nama', 'pelatihan'], true)) {
                $map['nama'] = $colIndex;
            } elseif (preg_match('/^(angkatan|batch|gelombang)$/i', $val)) {
                $map['angkatan'] = $colIndex;
            } elseif (preg_match('/(terdaftar|pendaftar|registered)/i', $val)) {
                $map['terdaftar'] = $colIndex;
            } elseif (preg_match('/(lulus|realisasi|alumni)/i', $val)) {
                $map['lulus'] = $colIndex;
            } elseif (preg_match('/(peserta|target|jumlah|kuota)/i', $val)) {
                $map['peserta'] = $colIndex;
            } elseif (preg_match('/^(jp|jam.*pelajaran)$/i', $val)) {
                $map['jp'] = $colIndex;
            } elseif (preg_match('/(durasi|lama|hari|duration)/i', $val)) {
                $map['durasi'] = $colIndex;
            } elseif (preg_match('/(mulai|start|tgl.*mulai|tanggal.*mulai)/i', $val)) {
                $map['mulai'] = $colIndex;
            } elseif (preg_match('/(selesai|akhir|end|tgl.*selesai|tanggal.*selesai)/i', $val)) {
                $map['selesai'] = $colIndex;
            } elseif (preg_match('/(sumber.*dana|dana|anggaran|funding)/i', $val)) {
                $map['sumber_dana'] = $colIndex;
            } elseif (preg_match('/(tempat|lokasi|pelaksana|venue)/i', $val)) {
                $map['tempat'] = $colIndex;
            } elseif (preg_match('/(penyelenggara|unit|bidang)/i', $val)) {
                $map['penyelenggara'] = $colIndex;
            } elseif (preg_match('/(kontak.*pic|hp.*pic|telp.*pic|no.*pic)/i', $val)) {
                $map['pic_kontak'] = $colIndex;
            } elseif (preg_match('/^(pic|narahubung|pic.*nama)$/i', $val)) {
                $map['pic_nama'] = $colIndex;
            } elseif (preg_match('/(link|url|pendaftaran|lms)/i', $val)) {
                $map['link'] = $colIndex;
            } elseif (preg_match('/(deskripsi|keterangan|catatan)/i', $val)) {
                $map['deskripsi'] = $colIndex;
            } elseif (preg_match('/(jadwal.*tahap|tahapan|rincian.*tahap)/i', $val)) {
                $map['tahapan'] = $colIndex;
            } elseif (preg_match('/(seleksi.*hari|h-)/i', $val)) {
                $map['seleksi_hari'] = $colIndex;
            } elseif (preg_match('/(metode|tahap|pembelajaran)/i', $val)) {
                $map['metode'] = $colIndex;
            }
        }

        if (isset($map['nama']) && (isset($map['mulai']) || isset($map['peserta']) || isset($map['tahapan']))) {
            return $map;
        }

        return null;
    }

    /**
     * Fallback standard column map if headers not explicitly detected.
     */
    protected static function fallbackColumnMap(array $firstRow): array
    {
        return [
            'kategori'    => 0,
            'nama'        => 1,
            'peserta'     => 2,
            'durasi'      => 3,
            'mulai'       => 4,
            'selesai'     => 5,
            'sumber_dana' => 6,
            'tempat'      => 7,
            'metode'      => 8,
            'tahapan'     => 9,
        ];
    }

    /**
     * Parse date from various Excel/user formats.
     */
    public static function parseDate($value, ?int $defaultYear = null): ?Carbon
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            $num = (float)$value;
            if ($num > 30000 && $num < 70000) {
                try {
                    $dateTime = ExcelDate::excelToDateTimeObject($num);
                    return Carbon::instance($dateTime)->startOfDay();
                } catch (\Exception $e) {
                }
            }
        }

        $str = trim((string)$value);

        $indoMonths = [
            'januari' => 'January', 'jan' => 'Jan',
            'februari' => 'February', 'feb' => 'Feb',
            'maret' => 'March', 'mar' => 'Mar',
            'april' => 'April', 'apr' => 'Apr',
            'mei' => 'May',
            'juni' => 'June', 'jun' => 'Jun',
            'juli' => 'July', 'jul' => 'Jul',
            'agustus' => 'August', 'agt' => 'Aug', 'ags' => 'Aug',
            'september' => 'September', 'sep' => 'Sep', 'sept' => 'Sep',
            'oktober' => 'October', 'okt' => 'Oct',
            'november' => 'November', 'nov' => 'Nov',
            'desember' => 'December', 'des' => 'Dec',
        ];

        $lower = strtolower($str);
        foreach ($indoMonths as $indo => $eng) {
            $lower = preg_replace('/\b' . preg_quote($indo, '/') . '\b/i', $eng, $lower);
        }

        // If year missing (e.g., "14 Sep"), append default year
        if ($defaultYear && !preg_match('/\b20\d{2}\b/', $lower)) {
            $lower .= ' ' . $defaultYear;
        }

        $formats = [
            'Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d',
            'd M Y', 'd F Y', 'j M Y', 'j F Y',
            'm/d/Y', 'd.m.Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $parsed = Carbon::createFromFormat($fmt, $str);
                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Exception $e) {
            }
        }

        try {
            return Carbon::parse($lower)->startOfDay();
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Compute training status based on current date.
     */
    public static function computeStatus(Carbon $mulai, Carbon $selesai, int $seleksiHari = 7): string
    {
        $today        = Carbon::today();
        $seleksiDays  = max(0, $seleksiHari);
        $seleksiStart = $mulai->copy()->subDays($seleksiDays);

        if ($today->gt($selesai)) {
            return 'Selesai';
        } elseif ($today->gte($mulai) && $today->lte($selesai)) {
            return 'Sedang Berlangsung';
        } elseif ($seleksiDays > 0 && $today->gte($seleksiStart) && $today->lt($mulai)) {
            return 'Tahap Seleksi';
        }

        return 'Belum Dimulai';
    }
}
