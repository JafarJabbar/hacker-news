<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostResourceList;
use App\Models\Post;
use Illuminate\Http\Request;
use Cache;
use Validator;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Get all posts
     */
    public function getPosts(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = [];
        $POSTS_LIST_CACHE_NAME = "POSTS_PAGE_" . $request->page;
        $posts_lists = Cache::get($POSTS_LIST_CACHE_NAME);
//        if (!$posts_lists) {
        $posts_lists = Post::orderBy('upvotes', "DESC")->paginate(30);
        Cache::put($POSTS_LIST_CACHE_NAME, $posts_lists);
//        }
        if (!$posts_lists) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => PostResourceList::collection($posts_lists)
        ]);
    }

    /**
     * Get post by ID
     */
    public function getPostByID(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = (int)$request->id;
        if (!$id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }
        $POST_CONTENT_CACHE_NAME = "POST_CONTENT_" . $request->id;
        $post_content = Cache::get($POST_CONTENT_CACHE_NAME);
        if (!$post_content) {
            $post_content = Post::find($id);
            Cache::put($POST_CONTENT_CACHE_NAME, $post_content);
        }
        if (!$post_content) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => new PostResource($post_content)
        ]);
    }

    /**
     * Create new post
     */
    public function createPost(Request $request)
    {
        $rules = [
            'title' => 'required|min:3|max:255',
            'link' => 'required|min:3|max:255',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'code' => 400,
                'title' => $validator->errors()->first()
            ]);
        }
        $fields = [
            'title' => $request->title,
            'link' => $request->link,
        ];

        $post = new Post();
        $post->title = $fields['title'];
        $post->link = $fields['link'];
        $post->author_id = $request->user()->id;
        $post->save();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => new PostResource($post)
        ]);
    }

    /**
     * UPDATE  post
     */
    public function updatePost(Request $request)
    {
        $id = (int)$request->id;
        if (!$id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }

        $rules = [
            'title' => 'required|min:3|max:255',
            'link' => 'required|min:3|max:255',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response([
                'status' => false,
                'code' => 400,
                'title' => $validator->errors()->first()
            ]);
        }
        $fields = [
            'title' => $request->title,
            'link' => $request->link,
        ];
        $post = Post::find($id);
        if ($post->author_id != $request->user()->id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "You have not permission for this action"
            ]);
        }

        $post->title = $fields['title'];
        $post->link = $fields['link'];
        $post->save();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => new PostResource($post)
        ]);
    }

    /**
     * Delete new post
     */
    public function deletePost(Request $request)
    {
        $id = (int)$request->id;
        if (!$id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        if ($post->author_id != $request->user()->id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "You have not permission for this action"
            ]);
        }

        $post->delete();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'title' => "Successfully deleted"
        ]);
    }

    /**
     * Upvote post
     */
    public function upvotePost(Request $request)
    {
        $id = (int)$request->id;
        if (!$id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }

        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        if ($post->author_id == $request->user()->id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "You can not upvote own post"
            ]);
        }

        $ifExist = DB::table('users_vote_posts')
            ->where([['post_id', $post->id], ['user_id', $request->user()->id]])
            ->first();
        if ($ifExist) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Oops... You already voted"
            ]);
        }
        DB::table('users_vote_posts')->insertGetId([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);
        $post->upVotes = $post->upvotes;
        $post->save();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'title' => "Upvoted"
        ]);
    }
}
