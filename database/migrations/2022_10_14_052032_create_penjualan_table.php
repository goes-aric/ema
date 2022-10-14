<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jual', 150);
            $table->date('tanggal');
            $table->decimal('nominal',12,2);
            $table->text('uraian')->nullable();
            $table->string('kode_akun_persediaan', 150);
            $table->string('kode_akun_penerimaan', 150);
            $table->string('kode_user', 150);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualan');
    }
};
