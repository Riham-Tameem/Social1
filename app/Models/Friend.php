<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;
    protected $fillable=[
        'friend_id',
        'user_id'
    ];
    function users(){
        return $this->belongsTo(User::class,'friend_id');
    }
}
