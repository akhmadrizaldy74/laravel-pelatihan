<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use App\Models\MasterKategori;
use App\Models\MasterMetode;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['nama' => 'PELATIHAN FUNGSIONAL', 'urutan' => 1],
            ['nama' => 'PELATIHAN MANAJERIAL', 'urutan' => 2],
            ['nama' => 'PELATIHAN TEKNIS', 'urutan' => 3],
            ['nama' => 'PELATIHAN KEPEMIMPINAN', 'urutan' => 4],
            ['nama' => 'PELATIHAN SOSIAL KULTURAL', 'urutan' => 5],
        ];

        foreach ($kategoris as $kat) {
            MasterKategori::updateOrCreate(
                ['nama' => $kat['nama']],
                ['urutan' => $kat['urutan'], 'is_active' => true]
            );
        }

        $metodes = [
            ['nama' => 'Online/Daring', 'kode_warna' => '#38bdf8', 'warna_teks' => '#0c4a6e', 'urutan' => 1],
            ['nama' => 'Klasikal', 'kode_warna' => '#fb923c', 'warna_teks' => '#ffffff', 'urutan' => 2],
            ['nama' => 'PKL + Bimbingan', 'kode_warna' => '#c084fc', 'warna_teks' => '#ffffff', 'urutan' => 3],
            ['nama' => 'Seminar + Penutupan', 'kode_warna' => '#facc15', 'warna_teks' => '#713f12', 'urutan' => 4],
            ['nama' => 'Mandiri/MOOC', 'kode_warna' => '#4ade80', 'warna_teks' => '#14532d', 'urutan' => 5],
            ['nama' => 'Assessment', 'kode_warna' => '#f472b6', 'warna_teks' => '#ffffff', 'urutan' => 6],
            ['nama' => 'Libur Hari Raya Nyepi dan Idul Fitri', 'kode_warna' => '#b91c1c', 'warna_teks' => '#ffffff', 'urutan' => 7],
        ];

        foreach ($metodes as $metode) {
            MasterMetode::updateOrCreate(
                ['nama' => $metode['nama']],
                [
                    'kode_warna' => $metode['kode_warna'],
                    'warna_teks' => $metode['warna_teks'],
                    'urutan'     => $metode['urutan'],
                    'is_active'  => true,
                ]
            );
        }

        HariLibur::updateOrCreate(
            ['tahun' => 2026, 'nama' => 'Libur Hari Raya Nyepi dan Idul Fitri'],
            [
                'tanggal_mulai'   => '2026-03-18',
                'tanggal_selesai' => '2026-03-24',
                'keterangan'      => 'Tanggal 18 - 24 Maret Libur dan Cuti Bersama (Nyepi dan Idul Fitri)',
                'warna'           => '#b91c1c',
                'is_active'       => true,
            ]
        );
    }
}
