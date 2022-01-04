<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmToken extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['device_type','device_id','status','user_id','fcm_token'];
}
