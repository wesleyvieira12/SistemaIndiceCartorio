<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Protocolo;
use DB;

class ProtocoloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Protocolo $p)
    {
        $protocolos = $p->orderBy('id', 'desc')->paginate(10);
        return view('admin.protocolo.index',compact('protocolos'));
    }

    public function procura(Request $request,Protocolo $p)
    {
        $dataform = $request->except('_token');
        
            $protocolos = $p->where('nome_representante', 'like', '%'.$request->pesquisa_nome.'%')
                            ->orwhere('nome_empresa', 'like', '%'.$request->pesquisa_nome.'%')
                            ->orwhere('cpf_representante', 'like', '%'.$request->pesquisa_nome.'%')
                            ->orwhere('cnpj_empresa', 'like', '%'.$request->pesquisa_nome.'%')
                            ->orderBy('id', 'desc')->paginate(10);
        
       
        return view('admin.protocolo.index',compact('protocolos','dataform'));
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Protocolo $protocolo)
    {
        return view('admin.protocolo.create', compact('protocolo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //'anotacao','cnpj_empresa','cpf_representante','folha','livro','nome_representante','nome_empresa','registro','tipo'
        
        DB::beginTransaction();

        //validar formulario
        $request->validate([
            'registro' =>'required',
            'livro' =>'required'
        ]);

        //Criando usuario
        $protocolo = new Protocolo;
        $protocolo->anotacao = $request->anotacao;
        $protocolo->cnpj_empresa = $request->cnpj_empresa;
        $protocolo->cpf_representante = $request->cpf_representante;
        $protocolo->folha = $request->folha;
        $protocolo->livro = $request->livro;
        $protocolo->nome_representante = $request->nome_representante;
        $protocolo->nome_empresa = $request->nome_empresa;
        $protocolo->registro = $request->registro;
        $protocolo->tipo = $request->tipo;
        $protocolo->save();
        
        //Criação de log
        $log = auth()->user()->logs()->create([
            'informacao' => 'Protocolo: id#'.$protocolo->id.' foi criado'
        ]);

        //verificar se salvou tudo
        if($protocolo && $log){
            DB::commit();
            return redirect()->route('protocolos.create')->with('message', 'Protocolo cadastrado com sucesso!');
        }else{
            DB::rollback();
            return redirect()->route('protocolos.create')->with('message', 'Falha no cadastro!');
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $protocolo = Protocolo::find($id);
        return view('admin.protocolo.edit',compact('protocolo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        //validar formulario
        $request->validate([
            'registro' =>'required',
            'livro' =>'required'
        ]);
        
        //alterando usuario
        $protocolo = Protocolo::find($id);
        $protocolo->anotacao = $request->anotacao;
        $protocolo->cnpj_empresa = $request->cnpj_empresa;
        $protocolo->cpf_representante = $request->cpf_representante;
        $protocolo->folha = $request->folha;
        $protocolo->livro = $request->livro;
        $protocolo->nome_representante = $request->nome_representante;
        $protocolo->nome_empresa = $request->nome_empresa;
        $protocolo->registro = $request->registro;
        $protocolo->tipo = $request->tipo;
        $protocolo->save();

        //salvar log
        $log = auth()->user()->logs()->create([
            'informacao' => 'Protocolo '.$protocolo->nome_empresa.$protocolo->nome_representante.' foi editado'
        ]);

        //verificar se salvou tudo
        if($protocolo && $log){
            DB::commit();
            return redirect()->route('protocolos.index')->with('message', 'Protocolo alterado com sucesso!');  
        }else{
            DB::rollback();
            return redirect()->route('protocolos.index')->with('message', 'Falha na alteração!');  
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        $protocolo = Protocolo::findOrFail($id);
        $protocolo->delete();

        $log = auth()->user()->logs()->create([
            'informacao' => 'Protocolo de '.$protocolo->nome_representante.$protocolo->nome_empresa.' excluido'
        ]);

        if($protocolo && $log){
            DB::commit();
            return redirect()->route('protocolos.index')->with('message', 'Protocolo EXCLUIDO com sucesso!');  
        }else{
            DB::rollback();
            return redirect()->route('protocolos.index')->with('message', 'Falha na exclusão!');  
        }
    }
}
