<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Permission;
use App\Role;
use App\Models\Log;
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function types($type = null){
        $types =[
            'N' => 'Normal',
            'A' => 'Administrador'
        ];
        if(!$type){
            return $types;
        }

        return $types[$type];
    }

    public function logs(){
        return $this->hasMany(Log::class,'usuario_id');
    }

    public function roles(){
        return $this->belongsToMany(Role::class);
    }

    public function hasPermission(Permission $permission){

        return $this->hasAnyRoles($permission->roles);
    }

    public function hasAnyRoles($roles){
        if(is_array($roles) || is_object($roles)){
            return !! $roles->intersect($this->roles)->count();
        }else{
            return $this->roles->contains('name',$roles);
        }
    }


}
