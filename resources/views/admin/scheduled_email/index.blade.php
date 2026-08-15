@extends('adminlte::page')

@section('title', 'E-mails agendados')

@section('content_header')
    <h1>E-mails agendados</h1>
@stop

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <a href="{{ route('emails-agendados.create') }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Agendar e-mail
            </a>
        </div>
        <div class="box-body">
            @include('includes.alerts')
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th style="width: 50px">#</th>
                    <th>Título</th>
                    <th>Agendado para</th>
                    <th>Destinatários</th>
                    <th>Status</th>
                    <th style="width: 180px">Ações</th>
                </tr>
                </thead>
                <tbody>
                @forelse($emails as $email)
                    <tr>
                        <td>{{ $email->id }}</td>
                        <td>{{ $email->subject }}</td>
                        <td>{{ $email->scheduled_at ? $email->scheduled_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ count($email->recipientList()) }}</td>
                        <td>
                            @if($email->status === 'pending')
                                <span class="label label-warning">{{ $email->statusLabel() }}</span>
                            @elseif($email->status === 'sent')
                                <span class="label label-success">{{ $email->statusLabel() }}</span>
                            @elseif($email->status === 'failed')
                                <span class="label label-danger">{{ $email->statusLabel() }}</span>
                            @else
                                <span class="label label-default">{{ $email->statusLabel() }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('emails-agendados.show', $email->id) }}" class="btn btn-xs btn-info" title="Ver">
                                <i class="fa fa-eye"></i>
                            </a>
                            @if($email->isEditable())
                                <a href="{{ route('emails-agendados.edit', $email->id) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('emails-agendados.cancel', $email->id) }}" method="post" style="display:inline">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-xs btn-default" title="Cancelar" onclick="return confirm('Cancelar este agendamento?')">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('emails-agendados.destroy', $email->id) }}" method="post" style="display:inline">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-xs btn-danger" title="Excluir" onclick="return confirm('Excluir este registro?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Nenhum e-mail agendado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {{ $emails->links() }}
        </div>
    </div>
@stop
