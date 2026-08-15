@extends('adminlte::page')

@section('title', 'Detalhe do e-mail agendado')

@section('content_header')
    <h1>E-mail agendado #{{ $email->id }}</h1>
@stop

@section('content')
    <div class="box">
        <div class="box-body">
            <p><strong>Status:</strong> {{ $email->statusLabel() }}</p>
            <p><strong>Agendado para:</strong> {{ $email->scheduled_at ? $email->scheduled_at->format('d/m/Y H:i') : '-' }}</p>
            <p><strong>Enviado em:</strong> {{ $email->sent_at ? $email->sent_at->format('d/m/Y H:i') : '-' }}</p>
            <p><strong>Título:</strong> {{ $email->subject }}</p>
            <p><strong>Destinatários:</strong></p>
            <ul>
                @foreach($email->recipientList() as $to)
                    <li>{{ $to }}</li>
                @endforeach
            </ul>
            <p><strong>Texto:</strong></p>
            <pre style="white-space: pre-wrap;">{{ $email->body }}</pre>
            @if($email->error_message)
                <div class="alert alert-danger">{{ $email->error_message }}</div>
            @endif
            <a href="{{ route('emails-agendados.index') }}" class="btn btn-default">Voltar</a>
            @if($email->isEditable())
                <a href="{{ route('emails-agendados.edit', $email->id) }}" class="btn btn-warning">Editar</a>
            @endif
        </div>
    </div>
@stop
