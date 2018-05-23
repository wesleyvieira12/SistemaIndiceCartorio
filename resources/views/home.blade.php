@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Informações</h1>
@stop

@section('content')
    <div class="box">
    	<div class="box-header">
    		
    	</div>
    	<div class="box-body">
    		<div class="col-lg-3 col-xs-6">
	          <!-- small box -->
	          <div class="small-box bg-yellow">
	            <div class="inner">
	              <h3>{{ $n_protocolo }}</h3>

	              <p>Protocolos</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-file"></i>
	            </div>
	            <a href="#" class="small-box-footer">
	           </a>
	          </div>
	        </div>
	        <!-- ./col -->
    		<div class="col-lg-3 col-xs-6">
	          <!-- small box -->
	          <div class="small-box bg-blue">
	            <div class="inner">
	              <h3>{{ $n_user }}</h3>

	              <p>Usuarios</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-user"></i>
	            </div>
	            <a href="#" class="small-box-footer">
	           </a>
	          </div>
	        </div>
	        <!-- ./col -->

 	   	</div>
 	   	
 	   	
    </div>
@stop