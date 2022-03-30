<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $appends = ['upvotes', 'comments', 'authorName'];
    public $timestamps = false;

    public function getUpvotesAttribute()
    {
        $pivot_data = DB::table('users_vote_posts')->where('post_id', $this->id)->get();
        return count($pivot_data);
    }

    public function getCommentsAttribute()
    {
        return Comment::where('post_id', $this->id)->get();
    }

    public function getAuthorNameAttribute()
    {
        $author = User::where('id', $this->author_id)->first();
        return $author['name'];
    }
}
