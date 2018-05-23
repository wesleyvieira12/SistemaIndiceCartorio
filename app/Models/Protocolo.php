<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Protocolo extends Model
{
	protected $fillable = ['anotacao','cnpj_empresa','cpf_representante','folha','livro','nome_representante','nome_empresa','registro','tipo'];

    public $timestamps = false;
}
