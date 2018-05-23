@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Protocolos</h1>
@stop

@section('content')

    <div class="box">
            <div class="box-header with-border">
            <div class="col-lg-11">
              <form class="form-inline" action="{{ route('protocolos.procura') }}" method="post">
              {!! csrf_field() !!}
		           <div class="form-group">
			            <input type="text" name="pesquisa_nome" class="form-control">
		        	 </div>
		        	 <div class="form-group">
		        		<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Pesquisar</button>
		        	 </div>
              </form>
             </div>
	        	
	        	<a href="{{ route('protocolos.create')}}" class="btn btn-success"> <i class="fa fa-plus"></i> Protocolo</a>
	        	

            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
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
                    <td>
                    	<div class="col-lg-3">
                    		<a href="{{ route('protocolos.edit',$protocolo->id) }}" class="btn btn-warning"> <i class="fa fa-edit"></i></a> 
                    	</div>
                    	<div class="col-lg-2">
                    		<form method="post" name="myform" action="{{ route('protocolos.destroy',$protocolo->id) }}">
			                {{csrf_field()}}
			                <input name="_method" type="hidden" value="DELETE">
			                <button type="button" class="btn btn-danger" onclick="pergunta()"><i class="fa fa-trash"></i></button>
			            	</form>
                    	</div>
                    	
                    	
                    </td>
                  </tr>
                @endforeach
                
              </tbody></table>
            </div>
            <!-- /.box-body -->
            @if(!isset($dataform))
              {{$protocolos->links()}}
            @else
              {{$protocolos->appends($dataform)->links()}}
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