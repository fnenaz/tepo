<?php

namespace Database\Seeders;

use App\Models\CopingRecommendation;
use Illuminate\Database\Seeder;

class CopingRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $recommendations = [
            // FASE MANIK: Aktivitas penyalur energi terarah
            [
                'phase' => 'manic',
                'type' => 'activity',
                'title' => 'Merapikan ruang tidur secara bertahap',
                'description' => 'Salurkan energi berlebih dengan merapikan kamar secara terstruktur. Aktivitas fisik ringan di dalam ruangan membantu menurunkan stimulasi berlebih.',
            ],
            [
                'phase' => 'manic',
                'type' => 'activity',
                'title' => 'Jalan kaki santai atau peregangan 20 menit',
                'description' => 'Lakukan jalan kaki santai atau peregangan otot selama 15 hingga 20 menit. Hindari olahraga intensitas tinggi yang dapat memicu lonjakan adrenalin.',
            ],
            [
                'phase' => 'manic',
                'type' => 'activity',
                'title' => 'Menyusun satu prioritas tugas tunggal',
                'description' => 'Saat banyak ide muncul bersamaan, pilih hanya satu tugas kecil dan selesaikan sampai tuntas sebelum berpindah ke aktivitas lain.',
            ],
            [
                'phase' => 'manic',
                'type' => 'activity',
                'title' => 'Menunda transaksi belanja selama 24 jam',
                'description' => 'Jika muncul dorongan belanja berlebih, simpan barang di daftar tunggu dan tunda pembayaran minimal 24 jam untuk menjaga kontrol impuls.',
            ],
            [
                'phase' => 'manic',
                'type' => 'activity',
                'title' => 'Latihan pernapasan ritmis 4-7-8',
                'description' => 'Tarik napas 4 detik, tahan 7 detik, dan embuskan perlahan 8 detik. Ulangi 5 siklus untuk membantu memperlambat laju pikiran yang cepat.',
            ],

            // FASE DEPRESI: Pemulihan bertahap beban rendah
            [
                'phase' => 'depressive',
                'type' => 'activity',
                'title' => 'Paparan cahaya pagi selama 10 menit',
                'description' => 'Duduk di dekat jendela atau teras rumah selama 10 menit pada pagi hari untuk membantu menstabilkan ritme sirkadian tubuh.',
            ],
            [
                'phase' => 'depressive',
                'type' => 'activity',
                'title' => 'Mandi air hangat dan ganti pakaian bersih',
                'description' => 'Mulai dari langkah perawatan diri paling sederhana. Air hangat membantu meredakan ketegangan otot saat tubuh terasa sangat lemas.',
            ],
            [
                'phase' => 'depressive',
                'type' => 'activity',
                'title' => 'Mencatat tiga hal kecil yang berhasil dilakukan',
                'description' => 'Tuliskan tiga hal sederhana yang sudah Anda lewati hari ini, seperti bangun dari tempat tidur, minum air putih, atau meminum obat.',
            ],
            [
                'phase' => 'depressive',
                'type' => 'activity',
                'title' => 'Berbicara di ruang konsultasi sebaya anonim',
                'description' => 'Gunakan fitur konsultasi anonim untuk menceritakan beban pikiran tanpa perlu khawatir identitas Anda diketahui oleh lawan bicara.',
            ],
            [
                'phase' => 'depressive',
                'type' => 'music',
                'title' => 'Mendengarkan instrumen akustik bertempo tenang',
                'description' => 'Putar musik instrumental dengan tempo stabil untuk menemani waktu istirahat tanpa membebani pikiran.',
                'url' => null,
            ],
        ];

        foreach ($recommendations as $rec) {
            CopingRecommendation::create($rec);
        }
    }
}
