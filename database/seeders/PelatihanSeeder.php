<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelatihan;

class PelatihanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Pelatihan Pembentukan JF Pengawas Ketenagakerjaan A. 111', 30, '2026-10-07', '2026-11-11'],
            ['Pelatihan Pembentukan JF Pengawas Ketenagakerjaan A. 112', 30, '2026-10-27', '2026-12-03'],
            ['Pelatihan Pembentukan JF Mediator Hubungan Industrial A. 103', 30, '2026-09-07', '2026-10-09'],
            ['Pelatihan Pembentukan JF Mediator Hubungan Industrial A. 104', 30, '2026-09-23', '2026-10-27'],
            ['Pelatihan Pembentukan JF Pengantar Kerja A. 69', 30, '2026-09-28', '2026-10-29'],
            ['Pelatihan Pembentukan JF Pengantar Kerja A. 70', 30, '2026-10-15', '2026-11-12'],
            ['Pelatihan Pelat. Pembentukan JF Penguji K3 A. 5', 40, '2026-10-19', '2026-11-24'],
            ['Pelatihan Pembentukan JF Instruktur A. 5', 40, '2026-08-26', '2026-09-29'],
            ['Pelatihan Pembentukan JF Instruktur A. 6', 40, '2026-09-02', '2026-10-08'],
            ['Pelatihan Pembentukan JF Instruktur A. 7', 40, '2026-09-09', '2026-10-15'],
            ['Pelatihan Pembentukan JF Instruktur PTKJ', 15, '2026-09-14', '2026-10-20'],
            ['TOT Pengampu Materi WAS/UJI K3', 14, '2026-02-02', '2026-02-05'],
            ['TOT Pengampu Materi', 33, '2026-04-06', '2026-04-10'],
            ['Pelatihan PPK Tipe C', 30, '2026-04-13', '2026-04-17'],
            ['Pelatihan PBJ Level 1 A. 4', 30, '2026-10-22', '2026-11-06'],
            ['Pelatihan Pengelolaan Arsip Elektronik', 32, '2026-05-04', '2026-05-09'],
            ['Pelatihan Kepemimpinan Administrator A. 23', 40, '2026-04-13', '2026-08-28'],
            ['Pelatihan Kepemimpinan Pengawas A. 37', 35, '2026-05-11', '2026-09-24'],
            ['Pelatihan Dasar CPNS Gelombang I (A. 25, A. 26, A. 78, A.79)', 141, '2026-01-20', '2026-04-20'],
            ['Pelatihan Dasar CPNS Gelombang II (A. 80, A. 81, A. 82, A.83)', 140, '2026-01-27', '2026-04-29'],
        ];

        foreach ($data as $item) {
            Pelatihan::create([
                'nama_pelatihan' => $item[0],
                'jumlah_peserta' => $item[1],
                'tanggal_mulai' => $item[2],
                'tanggal_selesai' => $item[3],
                'auto_status' => true,
            ]);
        }
    }
}
