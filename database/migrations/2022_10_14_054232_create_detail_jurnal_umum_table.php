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
        Schema::create('detail_jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->string('no_jurnal', 150);
            $table->string('kode_akun', 150);
            $table->string('nama_akun', 255);
            $table->decimal('debet', 12,2)->default(0);
            $table->decimal('kredit', 12,2)->default(0);
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
        Schema::dropIfExists('detail_jurnal_umum');
    }
};
