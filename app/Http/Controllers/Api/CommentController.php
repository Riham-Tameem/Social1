<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\CommentEloquent;
use Illuminate\Http\Request;

class CommentController extends BaseController
{
    public function __construct(CommentEloquent $commentEloquent)
    {
        $this->comment= $commentEloquent;
    }
    //************* comment ******************//////
    public function comment(Request $request){

        return $this->comment->comment($request->all());
    }

    public function editComment(Request $request,$comment_id)
    {
        return $this->comment->editComment($request->all(),$comment_id);
    }
    public function deleteComment($id)
    {
        return $this->comment->deleteComment($id);
    }
    ////////////********** end Comment ***********////////
}
