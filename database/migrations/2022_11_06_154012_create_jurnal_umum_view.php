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
                SELECT tanggal_transaksi, kode_akun, nama_akun, akun_utama, tipe_akun, arus_kas_tipe, SUM(debet) AS debet, SUM(kredit) AS kredit
                FROM (
                    SELECT
                        B.tanggal_transaksi,
                        A.no_jurnal,
                        A.kode_akun,
                        A.nama_akun,
                        C.akun_utama,
                        C.tipe_akun,
                        C.arus_kas_tipe,
                        A.debet,
                        A.kredit
                    FROM detail_jurnal_umum AS A
                    INNER JOIN jurnal_umum AS B ON A.no_jurnal = B.no_jurnal
                    INNER JOIN akun AS C ON A.kode_akun = C.kode_akun
                    WHERE B.deleted_at IS NULL
                    GROUP BY B.tanggal_transaksi, A.no_jurnal, A.kode_akun, A.nama_akun, C.akun_utama, C.tipe_akun, C.arus_kas_tipe, A.debet, A.kredit

                    UNION ALL

                    SELECT
                        tanggal_transaksi,
                        'XXX' AS no_jurnal,
                        'XXX' AS kode_akun,
                        'Laba Rugi Berjalan' AS nama_akun,
                        'XXX' AS akun_utama,
                        'EKUITAS' AS tipe_akun,
                        NULL AS arus_kas_tipe,
                        IFNULL(SUM(debet), 0) AS debet,
                        IFNULL(SUM(kredit), 0) AS kredit
                    FROM
                    (
                        SELECT
                            B.tanggal_transaksi,
                            0 AS debet,
                            (IFNULL(SUM(A.kredit), 0)-IFNULL(SUM(A.debet), 0)) AS kredit
                        FROM detail_jurnal_umum AS A
                        INNER JOIN jurnal_umum AS B ON A.no_jurnal = B.no_jurnal
                        INNER JOIN akun AS C ON A.kode_akun = C.kode_akun
                        WHERE C.tipe_akun = 'PENDAPATAN' AND B.deleted_at IS NULL
                        GROUP BY B.tanggal_transaksi, A.kode_akun, A.nama_akun

                        UNION ALL

                        SELECT
                            B.tanggal_transaksi,
                            (IFNULL(SUM(A.debet), 0)-IFNULL(SUM(A.kredit), 0)) AS debet,
                            0 AS kredit
                        FROM detail_jurnal_umum AS A
                        INNER JOIN jurnal_umum AS B ON A.no_jurnal = B.no_jurnal
                        INNER JOIN akun AS C ON A.kode_akun = C.kode_akun
                        WHERE C.tipe_akun = 'BEBAN' AND B.deleted_at IS NULL
                        GROUP BY B.tanggal_transaksi, A.kode_akun, A.nama_akun
                    ) AS TEMP
                    GROUP BY tanggal_transaksi
                ) AS tempTable
                GROUP BY tanggal_transaksi, kode_akun, nama_akun, akun_utama, tipe_akun, arus_kas_tipe
        ";
    }

    public function dropView()
    {
        return "
            DROP VIEW IF EXISTS view_jurnal_umum_data
        ";
    }
};
