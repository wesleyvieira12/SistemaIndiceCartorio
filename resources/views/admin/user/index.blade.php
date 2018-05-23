@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Usuários</h1>
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
	        	
	        		<a href="{{ route('users.create')}}" class="btn btn-success"> <i class="fa fa-plus"></i> Usuario</a>
	        	

            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
              <table class="table table-bordered">
                <tbody>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Ações</th>
                </tr>
                @foreach($users as $user)
                  <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('users.edit',$user->id) }}" class="btn btn-warning"> <i class="fa fa-edit"></i></a> <a href="{{ route('users.destroy',$user) }}" class="btn btn-danger"> <i class="fa fa-trash"></i></a></td>
                  </tr>
                @endforeach
                
              </tbody></table>
            </div>
            <!-- /.box-body -->
            {{$users->links()}}
          </div>
@stop