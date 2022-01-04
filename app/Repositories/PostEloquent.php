<?php

namespace App\Repositories;

use App\Http\Resources\LikeResource;
use App\Http\Resources\PostResource;
use App\Models\Favorite;
use App\Models\Friend;
use App\Models\Like;
use App\Models\Post;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PostEloquent extends BaseController
{
    private $model;
    private $notification;
    private $userEloquent;

    public function __construct(Post $post, NotificationEloquent $notificationEloquent, UserEloquent $userEloquent)
    {
        $this->model = $post;
        $this->notification = $notificationEloquent;
        $this->userEloquent = $userEloquent;
    }

    public function view(array $data)
    {
        // dd($data);
        $user = auth()->user()->id;
        $my_posts = Post::where('user_id', $user)->pluck('id')->toArray();
        $myFriend = Friend::where('user_id', $user)->pluck('friend_id')->toArray();
        $myFriend_posts = Post::whereIn('user_id', $myFriend)->pluck('id')->toArray();
        $total_posts = Post::whereIn('id', $my_posts)
            ->orWhereIn('id', $myFriend_posts)
            ->count();
        $page_size = $data['page_size'] ?? 10;
        //dd($page_size);
        $current_page = $data['current_page'] ?? 1;
        //$current_page=1;
        //$page_size=2;
        $total_page = ceil($total_posts / $page_size);
        $skip = $page_size * ($current_page - 1);
        $posts = Post::whereIn('id', $my_posts)
            ->orWhereIn('id', $myFriend_posts)
            ->skip($skip)
            ->take($page_size)
            ->get();
        // dd($total_page);
        return $this->sendResponse(
            'total_record => ' . $total_page . ',  page_number =>  ' . $current_page . ',   page_size  =>' . $page_size
            , PostResource::collection($posts));
    }

    public function index()
    {
        $post = Post::get();
        return $this->sendResponse('all posts', PostResource::collection($post));
    }

    public function store(array $data)
    {
        $authUser = Auth::user();
        $image = $data['image'] ?? null;
        if ($image != null) {
            $filename = $data['image']->store('public/images');
            $imagename = $data['image']->hashName();
            $data['image'] = $imagename;
        }
        $post = Post::create($data);
        $post->user_id = $authUser->id;
        if ($post->save()) {
            $friends = Friend::where('user_id', $authUser->id)->pluck('friend_id')->toArray();

            foreach ($friends as $friend_id)
                $this->notification->sendNotification($authUser->id, $friend_id, 'post', $post->id);
        }


        return $this->sendResponse('add post successfully', new PostResource($post));
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['error' => ' Invalid Post ID']);
        }
        return $this->sendResponse('Show post', new PostResource($post));
    }

    public function update(array $data, $id)
    {
        $user = auth()->user()->id;
        $post = Post::find($id);
        //dd($data);
        $image = $data['image'] ?? null;
        if ($user == $post->user_id) {
            if ($image != null) {
                // $image = $request->file('image');
                $filename = $data['image']->store('public/images');
                $imagename = $data['image']->hashName();
                $data['image'] = $imagename;
                $post->image = $data['image'];
            }
            $post->text = $data['text'];
            $post->update();
            ///$post->update($data);
            return $this->sendResponse('update post successfully', new PostResource($post));
        } else {
            return $this->sendError('you are un authorised');
        }
    }

    public function destroy($id)
    {
        $user = auth()->user()->id;

        $post = Post::find($id);
        if (!$post) {
            return $this->sendError('Invalid Post ID');
        }
        if ($user == $post->user_id) {
            $post->delete();
            return response()->json(['success' => 'Post deleted successfully']);
        } else {
            return $this->sendError('you are un authorised');
        }
    }

    public function like(array $data)
    {
        $request = $data['post_id'];
        $post = Post::find($request);
        $like = Like::where("post_id", "like", "%$request%")->first();
        if (!$post) {
            return $this->sendError('There is no Post has this id');

        } elseif ($like) {
            $like->delete();
            return response([
                'errorr' => '200',
                'message' => 'like has been delete ',
            ]);

        }
        $like = like::create([
            'post_id' => $request,
            'user_id' => Auth::user()->id,
        ]);
        return $this->sendResponse('like success', new LikeResource($like));
    }


    public function favourite(array $data)
    {

        $post = Post::find($data['post_id']);
        $user = Auth::user()->id;
        if (!$post) {
            return $this->sendError('There is no Post has this id');
        }
        $favourites = Favorite::create([
            'post_id' => $post->id,
            'user_id' => $user,
        ]);

        return $this->sendResponse('Post has been added to your favorites', $favourites);

    }
}
