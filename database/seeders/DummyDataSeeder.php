<?php

namespace Database\Seeders;

use App\Models\KlasifikasiSurat;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating dummy data...');

        // 1. Buat Klasifikasi Surat yang realistis
        $klasifikasi = $this->createKlasifikasi();
        $this->command->info('✅ Klasifikasi surat created: ' . $klasifikasi->count());

        // 2. Ambil user petugas yang sudah ada
        $petugas = User::role(['admin', 'operator'])->get();

        if ($petugas->isEmpty()) {
            $this->command->warn('⚠️  No admin/operator users found. Creating default users...');
            $this->call(UserSeeder::class);
            $petugas = User::role(['admin', 'operator'])->get();
        }
        $this->command->info('✅ Petugas available: ' . $petugas->count());

        // 3. Buat Surat Masuk masal
        $jumlahSuratMasuk = 20000;
        $this->command->info("📄 Creating {$jumlahSuratMasuk} surat masuk...");
        
        $progressBar = $this->command->getOutput()->createProgressBar($jumlahSuratMasuk);
        $progressBar->start();

        // Batch create untuk performa lebih baik
        collect(range(1, $jumlahSuratMasuk))->chunk(200)->each(function ($chunk) use ($klasifikasi, $petugas, $progressBar) {
            SuratMasuk::factory()
                ->count($chunk->count())
                ->recycle($klasifikasi)
                ->recycle($petugas)
                ->create();
            $progressBar->advance($chunk->count());
        });

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('✅ Surat masuk created: ' . $jumlahSuratMasuk);

        // 4. Buat Surat Keluar masal
        $jumlahSuratKeluar = 15000;
        $this->command->info("📄 Creating {$jumlahSuratKeluar} surat keluar...");

        $progressBar = $this->command->getOutput()->createProgressBar($jumlahSuratKeluar);
        $progressBar->start();

        collect(range(1, $jumlahSuratKeluar))->chunk(200)->each(function ($chunk) use ($klasifikasi, $petugas, $progressBar) {
            SuratKeluar::factory()
                ->count($chunk->count())
                ->recycle($klasifikasi)
                ->recycle($petugas)
                ->create();
            $progressBar->advance($chunk->count());
        });

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('✅ Surat keluar created: ' . $jumlahSuratKeluar);

        $this->command->newLine();
        $this->command->info('🎉 Dummy data seeding completed!');
    }

    /**
     * Create realistic klasifikasi surat data.
     */
    private function createKlasifikasi(): \Illuminate\Support\Collection
    {
        $data = [
            ['kode' => 'KP.01', 'nama' => 'Kepegawaian', 'keterangan' => 'Surat terkait kepegawaian dan SDM'],
            ['kode' => 'KU.01', 'nama' => 'Keuangan', 'keterangan' => 'Surat terkait keuangan dan anggaran'],
            ['kode' => 'UM.01', 'nama' => 'Umum', 'keterangan' => 'Surat umum dan korespondensi'],
            ['kode' => 'PJ.01', 'nama' => 'Perjanjian', 'keterangan' => 'Surat perjanjian, MoU, dan kontrak'],
            ['kode' => 'PR.01', 'nama' => 'Peraturan', 'keterangan' => 'Surat peraturan dan kebijakan'],
            ['kode' => 'LAP.01', 'nama' => 'Laporan', 'keterangan' => 'Surat laporan kegiatan dan evaluasi'],
            ['kode' => 'UND.01', 'nama' => 'Undangan', 'keterangan' => 'Surat undangan rapat dan acara'],
            ['kode' => 'SK.01', 'nama' => 'Surat Keputusan', 'keterangan' => 'SK dan surat penetapan'],
            ['kode' => 'IZ.01', 'nama' => 'Izin', 'keterangan' => 'Surat izin dan permohonan'],
            ['kode' => 'RET.01', 'nama' => 'Retensi', 'keterangan' => 'Surat dengan masa retensi khusus'],
        ];

        foreach ($data as $item) {
            KlasifikasiSurat::firstOrCreate(
                ['kode' => $item['kode']],
                array_merge($item, ['is_active' => true])
            );
        }

        return KlasifikasiSurat::where('is_active', true)->get();
    }
}
