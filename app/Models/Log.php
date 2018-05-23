<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Log extends Model
{
	protected $fillable = ['informacao','usuario_id'];
    
    public function user(){
    	return $this->belongsTo(User::class,'usuario_id');
    }

}
