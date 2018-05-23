<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Protocolo;
use Gate;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Protocolo $protocolo, User $user){

        /*$this->authorize('view_user',$user);
        if( Gate::denies('view_user',$user))
            abort(403,'Você não tem autorização!');
        */

        $n_protocolo = $protocolo->all()->count();
        $n_user = $user->all()->count();

        return view('home',compact('n_protocolo','n_user','user'));
    }
}
