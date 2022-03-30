<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Validator;
use Cache;

class CommentController extends Controller
{
    /**
     * Create new comment
     */
    public function createComment(Request $request)
    {
        $post_id = (int)$request->post_id;
        if (!$post_id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }

        $rules = [
            'content_body' => 'required|min:3|max:255',
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
            'content' => $request->content_body,
        ];
        $post = Post::select('id')->where('id', $post_id)->first();
        if (!$post) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        $comment = new Comment();
        $comment->content = $fields['content'];
        $comment->author_id = $request->user()->id;
        $comment->post_id = $post->id;
        $comment->save();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => new CommentResource($comment)
        ]);
    }


    /**
     * Delete comment
     */
    public function deleteComment(Request $request)
    {
        $id = (int)$request->id;
        if ($id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "Bad request"
            ]);
        }
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'title' => "Not found"
            ]);
        }
        if ($comment->author_id != $request->user()->id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'title' => "You have not permission for this action"
            ]);
        }
        $comment->delete();
        Cache::flush();
        return response()->json([
            'status' => true,
            'code' => 200,
            'title' => "Successfully deleted"
        ]);
    }
}
