<?php

namespace App\Console\Commands;

use App\Models\KlasifikasiSurat;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Console\Command;

class SeedDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dummy:seed 
                            {--surat-masuk=700 : Jumlah surat masuk yang akan dibuat}
                            {--surat-keluar=500 : Jumlah surat keluar yang akan dibuat}
                            {--fresh : Fresh database sebelum seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed dummy data untuk development (Surat Masuk & Surat Keluar)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!app()->environment('local', 'development', 'testing')) {
            $this->error('❌ Command ini hanya bisa dijalankan di environment local/development/testing!');
            return Command::FAILURE;
        }

        $jumlahSuratMasuk = (int) $this->option('surat-masuk');
        $jumlahSuratKeluar = (int) $this->option('surat-keluar');

        $this->info('');
        $this->info('🔧 Dummy Data Seeding');
        $this->info('=====================');
        $this->info("📄 Surat Masuk  : {$jumlahSuratMasuk}");
        $this->info("📄 Surat Keluar : {$jumlahSuratKeluar}");
        $this->info('');

        if ($this->option('fresh')) {
            $this->warn('⚠️  Running migrate:fresh...');
            $this->call('migrate:fresh', ['--seed' => true]);
            $this->info('');
        }

        // 1. Buat Klasifikasi Surat yang realistis
        $klasifikasi = $this->createKlasifikasi();
        $this->info('✅ Klasifikasi surat ready: ' . $klasifikasi->count());

        // 2. Ambil user petugas yang sudah ada
        $petugas = User::role(['admin', 'operator'])->get();

        if ($petugas->isEmpty()) {
            $this->warn('⚠️  No admin/operator users found!');
            $this->info('   Run: php artisan db:seed --class=UserSeeder');
            return Command::FAILURE;
        }
        $this->info('✅ Petugas available: ' . $petugas->count());
        $this->info('');

        // 3. Buat Surat Masuk
        $this->info("📄 Creating {$jumlahSuratMasuk} surat masuk...");
        $bar = $this->output->createProgressBar($jumlahSuratMasuk);
        $bar->start();

        collect(range(1, $jumlahSuratMasuk))->chunk(50)->each(function ($chunk) use ($klasifikasi, $petugas, $bar) {
            SuratMasuk::factory()
                ->count($chunk->count())
                ->recycle($klasifikasi)
                ->recycle($petugas)
                ->create();
            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info('✅ Surat masuk created: ' . $jumlahSuratMasuk);
        $this->newLine();

        // 4. Buat Surat Keluar
        $this->info("📄 Creating {$jumlahSuratKeluar} surat keluar...");
        $bar = $this->output->createProgressBar($jumlahSuratKeluar);
        $bar->start();

        collect(range(1, $jumlahSuratKeluar))->chunk(50)->each(function ($chunk) use ($klasifikasi, $petugas, $bar) {
            SuratKeluar::factory()
                ->count($chunk->count())
                ->recycle($klasifikasi)
                ->recycle($petugas)
                ->create();
            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info('✅ Surat keluar created: ' . $jumlahSuratKeluar);

        $this->newLine();
        $this->info('🎉 Dummy data seeding completed!');
        $this->info('');
        $this->table(
            ['Type', 'Count'],
            [
                ['Surat Masuk', SuratMasuk::count()],
                ['Surat Keluar', SuratKeluar::count()],
                ['Klasifikasi', KlasifikasiSurat::count()],
            ]
        );

        return Command::SUCCESS;
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
