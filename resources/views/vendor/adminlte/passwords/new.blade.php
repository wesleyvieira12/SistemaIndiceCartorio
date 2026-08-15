@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/auth.css') }}">
@stop

@section('body_class', 'login-page')

@section('body')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url(config('adminlte.dashboard_url', 'painel')) }}">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</a>
        </div>
        <div class="login-box-body">
            <p class="login-box-msg">Defina sua nova senha</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p style="margin:0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p class="text-muted" style="margin-bottom: 15px;">Conta: <strong>{{ $email }}</strong></p>

            <form action="{{ route('password.new.update') }}" method="post">
                {!! csrf_field() !!}

                <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
                    <input type="password" name="password" class="form-control"
                           placeholder="Nova senha" required minlength="6">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="Repita a nova senha" required minlength="6">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    Salvar nova senha
                </button>
            </form>

            <div class="auth-links" style="margin-top: 15px;">
                <a href="{{ url(config('adminlte.login_url', 'login')) }}">Voltar ao login</a>
            </div>
        </div>
    </div>
@stop
