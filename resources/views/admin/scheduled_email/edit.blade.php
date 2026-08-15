@extends('adminlte::page')

@section('title', 'Editar e-mail agendado')

@section('content_header')
    <h1>Editar e-mail agendado #{{ $email->id }}</h1>
@stop

@section('content')
    <div class="box">
        <div class="box-body">
            @include('includes.alerts')
            <form method="post" action="{{ route('emails-agendados.update', $email->id) }}">
                <input type="hidden" name="_method" value="PUT">
                @include('admin.scheduled_email._form')
                <button type="submit" class="btn btn-info">Atualizar</button>
                <a href="{{ route('emails-agendados.index') }}" class="btn btn-default">Voltar</a>
            </form>
        </div>
    </div>
@stop
