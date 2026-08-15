@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Protocolos</h1>
@stop

@section('content')

    <div class="box">
            <div class="box-header with-border">
              <form class="form-inline" action="{{ route('protocolos.index')}}" method="get" style="display:inline-block;margin-right:8px;margin-bottom:8px">
                   <div class="form-group">
                        <input type="text" name="pesquisa_nome" class="form-control" placeholder="Pesquisar..." style="max-width:100%">
                     </div>
                     <div class="form-group">
                        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Pesquisar</button>
                     </div>
              </form>
                <a href="{{ route('protocolos.create')}}" class="btn btn-success" style="margin-bottom:8px"> <i class="fa fa-plus"></i> Protocolo</a>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
              <div class="table-responsive">
              <table class="table table-bordered">
                <tbody>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                  <th>CPF/CNPJ</th>
                  <th>Livro</th>
                  <th>Registro</th>
                  <th>Folha</th>
                  <th>Anotações</th>
                  <th>Tipo</th>
                  <th>Ações</th>
                </tr>
                @foreach($protocolos as $protocolo)
                  <tr>
                    <td>{{ $protocolo->id }}</td>
                    <td>{{ $protocolo->nome_representante==null? $protocolo->nome_empresa : $protocolo->nome_representante }}</td>
                    <td>{{ $protocolo->cpf_representante==null? $protocolo->cnpj_empresa : $protocolo->cpf_representante }}</td>
                    <td>{{ $protocolo->livro }}</td>
                    <td>{{ $protocolo->registro }}</td>
                    <td>{{ $protocolo->folha }}</td>
                    <td>{{ $protocolo->anotacao }}</td>
                    <td>{{ $protocolo->tipo }}</td>
                    <td style="white-space:nowrap">
                        <a href="{{ route('protocolos.edit',$protocolo->id) }}" class="btn btn-warning btn-sm"> <i class="fa fa-edit"></i></a>
                        <form method="post" name="myform" action="{{ route('protocolos.destroy',$protocolo->id) }}" style="display:inline">
                        {{csrf_field()}}
                        <input name="_method" type="hidden" value="DELETE">
                        <button type="button" class="btn btn-danger btn-sm" onclick="pergunta()"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                  </tr>
                @endforeach
                
              </tbody></table>
              </div>
            </div>
            <!-- /.box-body -->
            @if(!isset($dataForm))
              {{$protocolos->links()}}
            @else
              {{$protocolos->appends($dataForm)->links()}}
            @endif
          </div>
          <script language="JavaScript"> 
function pergunta(){ 
	
   if (confirm('Tem certeza que quer enviar este formulário?')){ 
      document.forms["myform"].submit(); 
   }
} 
</script>
@stop