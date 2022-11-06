<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    public function createView()
    {
        return "
            CREATE VIEW view_jurnal_umum_data AS
                SELECT tanggal_transaksi, kode_akun, nama_akun, akun_utama, tipe_akun, SUM(debet) AS debet, SUM(kredit) AS kredit
                FROM (
                    SELECT
                        B.tanggal_transaksi,
                        A.kode_akun,
                        A.nama_akun,
                        C.akun_utama,
                        C.tipe_akun,
                        A.debet,
                        A.kredit
                    FROM detail_jurnal_umum AS A
                    INNER JOIN jurnal_umum AS B ON A.no_jurnal = B.no_jurnal
                    INNER JOIN akun AS C ON A.kode_akun = C.kode_akun
                    GROUP BY B.tanggal_transaksi, A.kode_akun, A.nama_akun, C.akun_utama, C.tipe_akun, A.debet, A.kredit
                ) tempTable
                GROUP BY tanggal_transaksi, kode_akun, nama_akun, akun_utama, tipe_akun
        ";
    }

    public function dropView()
    {
        return "
            DROP VIEW IF EXISTS view_jurnal_umum_data
        ";
    }
};
