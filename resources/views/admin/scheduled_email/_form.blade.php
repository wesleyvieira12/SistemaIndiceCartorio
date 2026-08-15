{!! csrf_field() !!}

<div class="form-group {{ $errors->has('subject') ? 'has-error' : '' }}">
    <label>Título (assunto)</label>
    <input type="text" name="subject" class="form-control"
           value="{{ old('subject', $email->subject) }}" required maxlength="255">
    @if($errors->has('subject'))
        <span class="help-block">{{ $errors->first('subject') }}</span>
    @endif
</div>

<div class="form-group {{ $errors->has('body') ? 'has-error' : '' }}">
    <label>Texto do e-mail</label>
    <textarea name="body" id="body-editor" class="form-control" rows="10" required>{{ old('body', $email->body) }}</textarea>
    @if($errors->has('body'))
        <span class="help-block">{{ $errors->first('body') }}</span>
    @endif
</div>

<div class="form-group {{ $errors->has('recipients') ? 'has-error' : '' }}">
    <label>Destinatários</label>
    <textarea name="recipients" class="form-control" rows="4" required
              placeholder="um@email.com&#10;outro@email.com">{{ old('recipients', $email->recipients) }}</textarea>
    <p class="help-block">Um e-mail por linha ou separados por vírgula.</p>
    @if($errors->has('recipients'))
        <span class="help-block">{{ $errors->first('recipients') }}</span>
    @endif
</div>

<div class="form-group {{ $errors->has('scheduled_at') ? 'has-error' : '' }}">
    <label>Data e hora do envio</label>
    @php
        $scheduledValue = old('scheduled_at');
        if (!$scheduledValue && !empty($email->scheduled_at)) {
            $scheduledValue = \Carbon\Carbon::parse($email->scheduled_at)->format('Y-m-d\TH:i');
        }
    @endphp
    <input type="datetime-local" name="scheduled_at" class="form-control" required
           value="{{ $scheduledValue }}">
    <p class="help-block">Fuso do servidor: America/Fortaleza</p>
    @if($errors->has('scheduled_at'))
        <span class="help-block">{{ $errors->first('scheduled_at') }}</span>
    @endif
</div>

<div class="form-group {{ $errors->has('repeat_interval') ? 'has-error' : '' }}">
    <label>Repetir</label>
    @php
        $repeatValue = old('repeat_interval', $email->repeat_interval ?: 'none');
    @endphp
    <select name="repeat_interval" class="form-control" required>
        @foreach(\App\Models\ScheduledEmail::repeatOptions() as $value => $label)
            <option value="{{ $value }}" {{ $repeatValue === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <p class="help-block">
        Após o envio, se repetir: agenda automaticamente o próximo dia, mês ou ano (mesmo horário).
    </p>
    @if($errors->has('repeat_interval'))
        <span class="help-block">{{ $errors->first('repeat_interval') }}</span>
    @endif
</div>
