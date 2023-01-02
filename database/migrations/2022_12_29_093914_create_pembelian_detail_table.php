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
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_pembelian')->unsigned();
            $table->foreign('id_pembelian')->references('id')->on('pembelian')->onDelete('cascade');
            $table->bigInteger('id_barang')->unsigned();
            $table->foreign('id_barang')->references('id')->on('barang')->onDelete('cascade');
            $table->decimal('harga', 20,2);
            $table->integer('qty');
            $table->decimal('total', 20,2);
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
        Schema::dropIfExists('pembelian_detail');
    }
};
