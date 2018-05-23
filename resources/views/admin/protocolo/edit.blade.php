@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Editar Protocolo</h1>
@stop

@section('content')

  <script language="javascript">
      function habilitacao(){
        if(document.getElementById('tipo_fisico').checked == false){
          document.getElementById("nome_empresa").disabled = false;
          document.getElementById("cnpj_empresa").disabled = false;
          document.getElementById("nome_representante").disabled = true;
          document.getElementById("cpf_representante").disabled = true;
        }
        if(document.getElementById('tipo_fisico').checked == true){
         document.getElementById("nome_empresa").disabled = true;
         document.getElementById("cnpj_empresa").disabled = true;
         document.getElementById("nome_representante").disabled = false;
         document.getElementById("cpf_representante").disabled = false;

        }
      }
  </script>

    <div class="box">
            <div class="box-header with-border">
            	
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
              <form method="post" action="{{ route('protocolos.update',$protocolo->id) }}">
                <input name="_method" type="hidden" value="PATCH">
                @include('admin.protocolo._form')
              
                           
              <div class="input-group input-group-sm">
        
                      <input type="submit" class="btn btn-info btn-flat" value="Alterar">
                    
              </div>
              <!-- /input-group -->
            </form>
            </div>
          </div>
@stop