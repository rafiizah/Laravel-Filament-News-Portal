<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'avatar',
        'bio'
    ];

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
