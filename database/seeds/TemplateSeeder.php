<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('templates')->truncate();

        // ---
        // --- Create default template
        // ---
        DB::table('templates')->insert([
            'name' => 'Default Template',
            'reference_table' => 't_defaul_0000001',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        // ---
        // --- Create some demo templates
        // ---
        DB::table('templates')->insert([
            'name' => 'Reminder - Normal Voice - Wanita',
            'reference_table' => 't_demo_0000002',
            'voice_text' => 'Halo BAF Friend terima kasih telah memilih BAF sebagai mitra pembiayaan Anda.\r\n\r\nNotifikasi ini sebagai pengingat lebih awal pembayaran angsuran [Produk] Anda untuk nomor perjanjian xxxxxxxxxxxx dengan nominal sebesar Rp x.xxx.xxx jatuh tempo pada (dd-mmm) yang sudah dapat dilakukan pembayaran melalui Kantor POS,Indomaret,Alfamart,Kantor jaringan BAF,Tokopedia, ATM BCA & Mandiri terdekat.\r\n\r\nKami ucapkan terima kasih apabila pembayaran sudah dilakukan. Informasi lebih lanjut silahkan hubungi 1500-750, Terima kasih.',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        DB::table('templates')->insert([
            'name' => 'OD 1 - Tegas - Pria',
            'reference_table' => 't_demo_0000003',
            'voice_text' => 'Halo.. Ini adalah notifikasi dari BAF.\r\n\r\nBerdasarkan catatan kami, saat ini tagihan angsuran [Produk]  Anda untuk  nomor perjanjian xxxxxxxxxxxx dengan nominal sebesar Rp x.xxx.xxx sudah jatuh tempo pada (dd-mmm).\r\n\r\nSegera lakukan pembayaran angsuran BAF hari ini melalui Alfamart,Kantor jaringan BAF,Tokopedia, ATM BCA & tempat pembayaran lain yang bekerjasama dengan BAF.\r\n\r\nInformasi lebih lanjut silahkan hubungi 1500-750, Terima kasih.',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        DB::table('templates')->insert([
            'name' => 'Reminder - Motor - Normal - Wanita',
            'reference_table' => 't_demo_0000004',
            'voice_text' => 'Halo BAF Friend terima kasih telah memilih BAF sebagai mitra pembiayaan Anda.\r\nNotifikasi ini sebagai pengingat lebih awal pembayaran angsuran kendaraan Motor Anda yang akan segera Jatuh tempo.\r\n\r\nPembayaran angsuran BAF dapat dilakukan melalui Alfamart,Kantor jaringan BAF,Tokopedia, ATM BCA & tempat pembayaran lain yang bekerjasama dengan BAF.\r\n\r\nAbaikan informasinya ini apabila pembayaran sudah dilakukan, Informasi lebih lanjut silahkan hubungi 1500-750, Terima kasih.',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        DB::table('templates')->insert([
            'name' => 'OD 1 - Motor - Early - Tegas - Pria',
            'reference_table' => 't_demo_0000005',
            'voice_text' => 'Halo.. Ini adalah notifikasi dari BAF.\r\nBerdasarkan catatan kami, saat ini tagihan angsuran kendaraan Motor Anda sudah lewai Jatuh tempo.\r\n\r\nSegera lakukan pembayaran angsuran BAF hari ini melalui Alfamart,Kantor jaringan BAF,Tokopedia, ATM BCA & tempat pembayaran lain yang bekerjasama dengan BAF.\r\n\r\nInformasi lebih lanjut silahkan hubungi 1500-750, Terima kasih.',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        DB::table('templates')->insert([
            'name' => 'OD 1 - Motor - DPD 15 up - Tegas - Pria',
            'reference_table' => 't_demo_0000006',
            'voice_text' => 'Halo.. Ini adalah notifikasi dari BAF.\r\n\r\nBerdasarkan catatan kami, saat ini tagihan angsuran kendaran Motor Anda sudah lewai Jatuh tempo.\r\n\r\nSegera lakukan pembayaran angsuran BAF hari ini , sebelum kami alihkan kepada penagih lapangan kami.',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);
    }
}
