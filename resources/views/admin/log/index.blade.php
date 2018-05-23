@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Logs</h1>
@stop

@section('content')
    <div class="box">
            <div class="box-header with-border">
            	<!--<div class="col-lg-6">
		            <div class="form-group col-lg-5">
			            <input type="text" name="pesquisa_nome" class="form-control">
		        	</div>
		        	<div class="form-group col-lg-3">
		        		<button class="btn btn-success"><i class="fa fa-search"></i></button>
		        	</div>
	        	</div>
          -->
	        	
	        		
	        	

            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table table-bordered">
                <tbody>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Informação</th>
                  <th>Usuario</th>
                  <th>Data</th>
                  
                </tr>
                @foreach($logs as $log)
                  <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->informacao }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $log->created_at }}</td>
                    
                  </tr>
                @endforeach
                
              </tbody></table>
            </div>
            <!-- /.box-body -->
            {{$logs->links()}}
          </div>
@stop