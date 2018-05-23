@extends('adminlte::page')

@section('title', 'Sistema de Indice - Home')

@section('content_header')
    <h1>Editar Usuário</h1>
@stop

@section('content')
    <div class="box">
            <div class="box-header with-border">
                
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              @include('includes.alerts')
              <form method="post" action="{{ route('users.update',$user->id) }}">
              {{csrf_field()}}
                <input name="_method" type="hidden" value="PATCH">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" name="name" value="{{ old('name',$user->name) }}" placeholder="Digite seu Nome">
              </div>
              <br>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control" name="email" value="{{ old('email',$user->email) }}" placeholder="Digite seu melhor email">
              </div>
              <br>

              <div class="input-group col-lg-2">
                <span class="input-group-addon"><i class="fa fa-group"></i></span>
                <select class="form-control" name="type">
                  <option value="{{$user->roles->first()->id}}">{{$user->roles->first()->name}}</option>
                  @foreach($roles as $role)
                    @if($role->name != $user->roles()->first()->name)
                      <option value="{{$role->id}}">{{$role->name}}</option>
                    @endif
                  @endforeach
                </select>
              </div>
              <br>
              <label class="label-form">Se desejar alterar a senha, digite uma nova, caso não queira deixe em branco</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                <input type="password" class="form-control" name="password" placeholder="Digite uma Nova Senha">
              </div>
              <br>             
              <div class="input-group input-group-sm">
        
                      <input type="submit" class="btn btn-info btn-flat" value="Editar">
                    
              </div>
              <!-- /input-group -->
            </form>
            </div>
          </div>
@stop