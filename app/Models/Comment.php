<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';
    public $timestamps = false;
    protected $hidden = [
        'post_id',
        'author_id'
    ];

    public function getAuthorNameAttribute()
    {
        $author = User::where('id', $this->author_id)->first();
        return $author['name'];
    }
}
