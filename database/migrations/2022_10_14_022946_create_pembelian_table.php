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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 150);
            $table->date('tanggal');
            $table->string('metode_bayar', 50)->default('TUNAI')->comment('TUNAI/KREDIT');
            $table->bigInteger('id_supplier')->unsigned()->nullable();
            $table->foreign('id_supplier')->references('id')->on('supplier')->onDelete('cascade');
            $table->decimal('total', 20,2)->default(0);
            $table->decimal('diskon', 20,2)->default(0);
            $table->decimal('grand_total', 20,2)->default(0);
            $table->text('catatan')->nullable();
            $table->string('gambar', 255)->nullable();
            $table->bigInteger('id_user')->unsigned();
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('pembelian');
    }
};
