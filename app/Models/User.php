<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['comments', 'posts', 'votes'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getCommentsAttribute()
    {
        return Comment::where('author_id', $this->id)->get();
    }

    public function getPostsAttribute()
    {
        return Post::where('author_id', $this->id)->get();
    }

    /**
     * this function is optional you can delete if you want
     * */
    public function getVotesAttribute()
    {
        $upvoted_posts = [];
        $upvotes = DB::table('users_vote_posts')->where('user_id', $this->id)->get();
        foreach ($upvotes as $upvote) {
            array_push($upvoted_posts, Post::where('id', $upvote->post_id)->first());
        }
        return $upvoted_posts;
    }
}
