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
        Schema::create('jurnal_umum_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_jurnal_umum')->unsigned();
            $table->foreign('id_jurnal_umum')->references('id')->on('jurnal_umum')->onDelete('cascade');
            $table->bigInteger('id_akun')->unsigned();
            $table->foreign('id_akun')->references('id')->on('akun')->onDelete('cascade');
            $table->string('kode_akun', 150);
            $table->string('nama_akun', 255);
            $table->decimal('debet', 20,2)->default(0);
            $table->decimal('kredit', 20,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnal_umum_detail');
    }
};
