@extends('adminlte::page')

@section('title', 'Agendar e-mail')

@section('content_header')
    <h1>Agendar e-mail</h1>
@stop

@section('content')
    <div class="box">
        <div class="box-body">
            @include('includes.alerts')
            <form method="post" action="{{ route('emails-agendados.store') }}">
                @include('admin.scheduled_email._form')
                <button type="submit" class="btn btn-info">Salvar agendamento</button>
                <a href="{{ route('emails-agendados.index') }}" class="btn btn-default">Cancelar</a>
            </form>
        </div>
    </div>
@stop
