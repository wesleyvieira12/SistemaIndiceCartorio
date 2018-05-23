{!! csrf_field() !!}
<p><b>Escolha o tipo de pessoa:</b></p>
@if($protocolo->tipo=='F' || $protocolo->tipo==null)
  <input type="radio" name="tipo" onClick="habilitacao()" id="tipo_fisico" value="F" checked> Pessoa Física
  <br>
  <input type="radio" name="tipo" onClick="habilitacao()" id="tipo_juridico" value="J"> Pessoa Jurídica
  <br><br>
  <div class="row" id="representante">
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>Nome do Representante</b></span>
      <input type="text" class="form-control" id="nome_representante" value="{{ old('nome_representante',$protocolo->nome_representante) }}" name="nome_representante" required>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>CPF</b></span>
      <input type="text" class="form-control"  id="cpf_representante" value="{{ old('cpf_representante',$protocolo->cpf_representante) }}" name="cpf_representante" required>
    </div>
  </div>
</div>             
<br>

<div class="row" id="empresa">
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>Nome da Empresa</b></span>
      <input type="text" class="form-control" disabled id="nome_empresa" value="{{ old('nome_empresa',$protocolo->nome_empresa) }}" name="nome_empresa" required>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>CNPJ</b></span>
      <input type="text" class="form-control" disabled id="cnpj_empresa" value="{{ old('cnpj_empresa',$protocolo->cnpj_empresa) }}" name="cnpj_empresa" required>
    </div>
  </div>
</div>             
<br>
@else
  <input type="radio" name="tipo" onClick="habilitacao()" id="tipo_fisico" value="F"> Pessoa Física
  <br>
  <input type="radio" name="tipo" onClick="habilitacao()" id="tipo_juridico" value="J" checked> Pessoa Jurídica
  <br><br>
  <div class="row" id="representante">
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>Nome do Representante</b></span>
      <input type="text" class="form-control" disabled id="nome_representante" value="{{ old('nome_representante',$protocolo->nome_representante) }}" name="nome_representante" required>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>CPF</b></span>
      <input type="text" class="form-control" disabled id="cpf_representante" value="{{ old('cpf_representante',$protocolo->cpf_representante) }}" name="cpf_representante">
    </div>
  </div>
</div>             
<br>

<div class="row" id="empresa">
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>Nome da Empresa</b></span>
      <input type="text" class="form-control" id="nome_empresa" value="{{ old('nome_empresa',$protocolo->nome_empresa) }}" name="nome_empresa" required>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon"><b>CNPJ</b></span>
      <input type="text" class="form-control" id="cnpj_empresa" value="{{ old('cnpj_empresa',$protocolo->cnpj_empresa) }}" name="cnpj_empresa" >
    </div>
  </div>
</div>             
<br>
@endif




<div class="row">
  <div class="col-lg-4">
    <div class="input-group">
      <span class="input-group-addon"><b>Livro</b></span>
      <input type="text" class="form-control" id="livro" value="{{ old('livro',$protocolo->livro) }}" name="livro" required>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="input-group">
      <span class="input-group-addon"><b>Registro</b></span>
      <input type="text" class="form-control" id="registro" value="{{ old('registro',$protocolo->registro) }}" name="registro" required>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="input-group">
      <span class="input-group-addon"><b>Folha</b></span>
      <input type="text" class="form-control" id="folha" value="{{ old('folha',$protocolo->folha) }}" name="folha">
    </div>
  </div>
</div>
<br>
<div class="row">
  <div class="col-lg-12">
    <div class="form-group">
      <span><b>Anotações</b></span>
      <textarea class="form-control" name="anotacao">{{old('cpf_representante',$protocolo->cpf_representante)}}</textarea>
    </div>
  </div>
</div>
<br>