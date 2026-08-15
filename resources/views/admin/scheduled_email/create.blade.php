@extends('adminlte::page')

@section('title', 'Agendar e-mail')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@stop

@section('content_header')
    <h1>Agendar alerta</h1>
@stop

@section('content')
    <div class="box">
        <div class="box-body">
            @include('includes.alerts')
            <form method="post" action="{{ route('emails-agendados.store') }}" id="scheduled-email-form">
                @include('admin.scheduled_email._form')
                <button type="submit" class="btn btn-info">Salvar agendamento</button>
                <a href="{{ route('emails-agendados.index') }}" class="btn btn-default">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>
    @include('admin.scheduled_email._editor_js')
@stop
