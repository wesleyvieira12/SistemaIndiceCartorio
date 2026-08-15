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
            <p class="login-box-msg">Digite o código enviado ao seu e-mail</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p style="margin:0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.code.verify') }}" method="post">
                {!! csrf_field() !!}

                <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', isset($email) ? $email : '') }}"
                           placeholder="Email" required>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback {{ $errors->has('code') ? 'has-error' : '' }}">
                    <input type="text" name="code" class="form-control" inputmode="numeric"
                           autocomplete="one-time-code" maxlength="12"
                           placeholder="Código de 6 dígitos" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    Verificar código
                </button>
            </form>

            <div class="auth-links" style="margin-top: 15px;">
                <a href="{{ route('password.request') }}">Reenviar código</a>
                <br>
                <a href="{{ url(config('adminlte.login_url', 'login')) }}">Voltar ao login</a>
            </div>
        </div>
    </div>
@stop
