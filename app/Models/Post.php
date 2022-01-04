<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
class Post extends Model
{
    use HasFactory,HasApiTokens;

    protected $guarded = [];
    protected $appends = ['date'];

    public function getDateAttribute()
    {
       $date= Carbon::parse($this->created_at)->diffForHumans();
     return $date;
        // return asset('storage/images/' . $this->image);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
