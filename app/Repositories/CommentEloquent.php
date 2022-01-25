<?php

namespace App\Repositories;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentEloquent extends BaseController
{
    private $model;
    public function __construct(Comment $comment , NotificationEloquent $notificationEloquent)
    {
        $this->model = $comment;
        $this->notification = $notificationEloquent;
    }
    public function comment(array $data){
        $text =$data['text'];
        $find_post=$data['post_id'];
        $user=auth()->user()->id;
        $post=Post::find($find_post);
        if(! $post){
            return $this->sendError(  404,'There is no Post has this id');
        }
        $comments = Comment::create([
            'post_id' =>$post->id,
            'text' => $text,
            'user_id' => $user,
        ]);
        //dd( $user);
        if ($comments->save()) {
         //   dd('success');
            $this->notification->sendNotification($user, $post->user_id, 'comment', $comments->id);
        }
        return $this->sendResponse(  'comment success',new CommentResource($comments));
    }

    public function editComment(array $data ,$comment_id)
    {
        $comment=Comment::find($comment_id);
        $user=auth()->user()->id;
        if(!$comment ){
            return $this->sendError(  404,'There is no Comment has this id');
        }
        else{
            if($user == $comment->user_id){
                $comment->text = $data['text'];
                $comment->save();
                return $this->sendResponse( 'comment updated success', new CommentResource($comment),);
            }else{
                return $this->sendError(  401,'you are un authorised');
            }
        }
    }
    public function deleteComment($id)
    {
        $comment=Comment::find($id);
        $user=auth()->user()->id;
        if(!$comment ){
            return $this->sendError(  404,'There is no Comment has this id');
        }else {
            if ($user == $comment->user_id) {
                $comment->delete();
                return $this->sendResponse('the comment deleted successfully',[]);
                /*return response([
                    'error' => '200',
                    'message' => 'the comment deleted successfully',
                ]);*/
            } else {
                return $this->sendError(  401,'you are un authorised');
            }
        }
    }
}
