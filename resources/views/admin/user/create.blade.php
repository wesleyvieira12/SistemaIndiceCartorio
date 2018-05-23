@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Criar novo Usuário</h1>
@stop

@section('content')
    <div class="box">
            <div class="box-header with-border">
            	
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
              <form method="post" action="{{ route('users.store') }}">
                
                @include('admin.user._form')
                           
              <div class="input-group input-group-sm">
        
                      <input type="submit" class="btn btn-info btn-flat" value="Cadastrar">
                    
              </div>
              <!-- /input-group -->
            </form>
            </div>
          </div>
@stop