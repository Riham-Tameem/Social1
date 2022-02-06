<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function getImageAttribute($value)
    {
        return url('storage/'.$value);
    }

}
