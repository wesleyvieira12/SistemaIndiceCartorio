<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProtocolosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocolos', function (Blueprint $table) {
            $table->increments('id');
            $table->text('anotacao');
            $table->string('cnpj_empresa',50)->nullable();
            $table->string('cpf_representante',14)->nullable();
            $table->string('folha',50);
            $table->string('livro',20);
            $table->string('nome_empresa',250)->nullable();
            $table->string('nome_representante',250)->nullable();
            $table->string('registro',250);
            $table->char('tipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('protocolos');
    }
}
