<?php

namespace App\Http\Controllers;

use App\Models\ScheduledEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduledEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage_scheduled_emails');
    }

    public function index()
    {
        $emails = ScheduledEmail::orderBy('scheduled_at', 'desc')->paginate(15);

        return view('admin.scheduled_email.index', compact('emails'));
    }

    public function create()
    {
        $email = new ScheduledEmail();
        $email->scheduled_at = Carbon::now()->addHour();
        $email->repeat_interval = 'none';

        return view('admin.scheduled_email.create', compact('email'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $email = ScheduledEmail::create([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipients' => $data['recipients'],
            'scheduled_at' => $data['scheduled_at'],
            'repeat_interval' => $data['repeat_interval'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        auth()->user()->logs()->create([
            'informacao' => 'E-mail agendado #'.$email->id.' — '.$email->subject,
        ]);

        return redirect()
            ->route('emails-agendados.index')
            ->with('message', 'E-mail agendado com sucesso!');
    }

    public function show($id)
    {
        $email = ScheduledEmail::findOrFail($id);

        return view('admin.scheduled_email.show', compact('email'));
    }

    public function edit($id)
    {
        $email = ScheduledEmail::findOrFail($id);

        if (! $email->isEditable()) {
            return redirect()
                ->route('emails-agendados.index')
                ->with('message', 'Só é possível editar e-mails ainda agendados.');
        }

        return view('admin.scheduled_email.edit', compact('email'));
    }

    public function update(Request $request, $id)
    {
        $email = ScheduledEmail::findOrFail($id);

        if (! $email->isEditable()) {
            return redirect()
                ->route('emails-agendados.index')
                ->with('message', 'Só é possível editar e-mails ainda agendados.');
        }

        $data = $this->validatedData($request);

        $email->update([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipients' => $data['recipients'],
            'scheduled_at' => $data['scheduled_at'],
            'repeat_interval' => $data['repeat_interval'],
        ]);

        auth()->user()->logs()->create([
            'informacao' => 'E-mail agendado #'.$email->id.' foi atualizado',
        ]);

        return redirect()
            ->route('emails-agendados.index')
            ->with('message', 'Agendamento atualizado!');
    }

    public function destroy($id)
    {
        $email = ScheduledEmail::findOrFail($id);
        $email->delete();

        auth()->user()->logs()->create([
            'informacao' => 'E-mail agendado #'.$id.' foi excluído',
        ]);

        return redirect()
            ->route('emails-agendados.index')
            ->with('message', 'Agendamento excluído.');
    }

    public function cancel($id)
    {
        $email = ScheduledEmail::findOrFail($id);

        if ($email->status !== 'pending') {
            return redirect()
                ->route('emails-agendados.index')
                ->with('message', 'Apenas agendamentos pendentes podem ser cancelados.');
        }

        $email->status = 'cancelled';
        $email->save();

        return redirect()
            ->route('emails-agendados.index')
            ->with('message', 'Agendamento cancelado.');
    }

    /**
     * @return array
     */
    protected function validatedData(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipients' => 'required|string',
            'scheduled_at' => 'required|string',
            'repeat_interval' => 'required|in:none,day,month,year',
        ]);

        $tmp = new ScheduledEmail(['recipients' => $request->input('recipients')]);
        $list = $tmp->recipientList();

        if (count($list) === 0) {
            $validator->after(function ($v) {
                $v->errors()->add('recipients', 'Informe ao menos um e-mail válido.');
            });
        }

        $scheduledAt = null;
        $rawDate = trim((string) $request->input('scheduled_at'));
        foreach (['d/m/Y H:i', 'd/m/Y H:i:s', 'd/m/Y'] as $format) {
            try {
                $scheduledAt = Carbon::createFromFormat($format, $rawDate);
                if ($format === 'd/m/Y') {
                    $scheduledAt->startOfDay();
                }
                break;
            } catch (\Exception $e) {
                $scheduledAt = null;
            }
        }

        if (! $scheduledAt) {
            $validator->after(function ($v) {
                $v->errors()->add('scheduled_at', 'Use o formato dia/mês/ano hora:minuto (ex.: 14/08/2026 21:30).');
            });
        }

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $data = $validator->getData();

        return [
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipients' => implode("\n", $list),
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            'repeat_interval' => $data['repeat_interval'],
        ];
    }
}
