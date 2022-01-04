<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmNotification extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table='fcm_notifications';

    protected $fillable = [
        'sender_id', 'receiver_id', 'action', 'action_id'
    ];
}
