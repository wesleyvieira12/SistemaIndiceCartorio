<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Validation\Rule;
use App\Role;
use DB;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
    	$users = $user->paginate(10);
        return view('admin.user.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(User $user)
    {
    	$roles = Role::all();
        return view('admin.user.create',compact('roles','user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	
    	DB::beginTransaction();

    	//validar formulario
    	$request->validate([
		    'name' =>'required',
            'email' =>'email|unique:users|required',
            'password' => 'required|confirmed|min:6'
		]);

		//Criando usuario
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        //Relacionar o usuario a role
        $user->roles()->attach($request->type);
   		
        //Criação de log
        $log = auth()->user()->logs()->create([
			'informacao' => 'Usuario '.$user->name.' foi criado'
        ]);

        //verificar se salvou tudo
        if($user && $log){
        	DB::commit();
        	return redirect()->route('users.index')->with('message', 'Usuário cadastrado com sucesso!');
        }else{
        	DB::rollback();
        	return redirect()->route('users.index')->with('message', 'Falha no cadastro!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function show(User $User)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    	$user = User::find($id);
    	$roles = Role::all();
    	return view('admin.user.edit',compact('user','roles'));   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
    	DB::beginTransaction();

    	//validar formulario
    	$request->validate([
		    'name' =>'required',
            'email' => 
            [
				Rule::unique('users')->ignore($id, 'id'),
				'required',
				'email'
			],
            'password' => 'min:6'
		]);
    	
    	//alterando usuario
     	$user = User::find($id);
     	$nomeAntigo = $user->name;
        $user->name = $request->name;
        $user->email = $request->email;
    
    	//verificar se foi passado nova senha
        if($request->password == null){
        	$user->password = $user->password;	
        }else{
        	$user->password = bcrypt($request->password);
        }
        $user->save();

        //Relacionar o usuario a role
        $user->roles()->attach($request->type);

        //salvar log
        $log = auth()->user()->logs()->create([
			'informacao' => 'Usuario '.$nomeAntigo.' foi editado'
        ]);

        //verificar se salvou tudo
        if($user && $log){
        	DB::commit();
        	return redirect()->route('users.index')->with('message', 'Usuário alterado com sucesso!');  
        }else{
        	DB::rollback();
        	return redirect()->route('users.index')->with('message', 'Falha na alteração!');  
        }
         
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    	DB::beginTransaction();

    	$user = User::findOrFail($id);
    	$user->delete();

    	$log = auth()->user()->logs()->create([
			'informacao' => 'Usuario '.$user->name.' excluido'
        ]);

        if($user && $log){
        	DB::commit();
        	return redirect()->route('users.index')->with('message', 'Usuário EXCLUIDO com sucesso!');  
        }else{
        	DB::rollback();
        	return redirect()->route('users.index')->with('message', 'Falha na exclusão!');  
        }
    }
}
