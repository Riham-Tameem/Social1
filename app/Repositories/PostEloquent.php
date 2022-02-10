<?php

namespace App\Repositories;

use App\Http\Resources\LikeResource;
use App\Http\Resources\PostResource;
use App\Models\Favorite;
use App\Models\Friend;
use App\Models\Image;
use App\Models\Images;
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
    //private $userEloquent;

    public function __construct(Post $post, NotificationEloquent $notificationEloquent)
    {
        $this->model = $post;
        $this->notification = $notificationEloquent;
      //  $this->userEloquent = $userEloquent;
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
      //  dd($data);
        $authUser = Auth::user();
        /*$image = $data['image'] ?? null;
        if ($image != null) {
            $filename = $data['image']->store('public/images');
            $imagename = $data['image']->hashName();
            $data['image'] = $imagename;
        }*/
      //  $post= new Post();
       $post= Post::create([
            'text'=>$data['text'],
        ]);
      //  $post->text=$data['text'];
        $post->user_id = $authUser->id;
      //  dd($data['images']);
        if (isset($data['images']) ) {
            foreach ($data['images'] as $image) {
               $postImage = new Image();
                $postImage->post_id = $post->id;
               // dd($post->id);
             /*   $filename = $image->store('public/images');
                $imageName = $image->hashName();
                Image::updateOrCreate([
                    'image'=>$imageName,
                    'post_id' => $post->id,
                ]);*/
                $filename = $image->store('images');
               // $imagename = $image->hashName();
                $postImage->image = ($filename);
                $postImage->save();
            }
        }
        if ($post->save()) {
            $friends = Friend::where('user_id', $authUser->id)->pluck('friend_id')->toArray();

            foreach ($friends as $friend_id)
                $this->notification->sendNotification($authUser->id, $friend_id, 'post', $post->id);
        }
        return $this->sendResponse('add post successfully', new PostResource($post));
    }
    public function share(array $data)
    {
        $data['user_id']=Auth::user()->id;
        $post = Post::findOrFail($data['post_id']);
        $post=Post::create([
            'text'=>$data['text'],
            'user_id'=>$data['user_id'],
            'share_post_id'=>$post->id,
        ]);
        return $this->sendResponse('share post successfully', new PostResource($post));
    }
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->sendError(404,' Invalid Post ID');
          //  return response()->json(['error' => ' Invalid Post ID']);
        }
        return $this->sendResponse('Show post', new PostResource($post));
    }

    public function update(array $data, $id)
    {
        $user = auth()->user()->id;
        $post = Post::find($id);
        //dd($data);
       // $image = $data['image'] ?? null;
        if ($user == $post->user_id) {
           /* if ($image != null) {
                // $image = $request->file('image');
                $filename = $data['image']->store('public/images');
                $imagename = $data['image']->hashName();
                $data['image'] = $imagename;
                $post->image = $data['image'];
            }*/
            $post->text = $data['text'];
            if (isset($data['images']) ) {
                foreach ($data['images'] as $image) {
                    $postImage = Image::where('post_id',$post->id)->first();
                    //$postImage->post_id = $post->id;
                    // dd($post->id);
                    /*   $filename = $image->store('public/images');
                       $imageName = $image->hashName();
                       Image::updateOrCreate([
                           'image'=>$imageName,
                           'post_id' => $post->id,
                       ]);*/
                    $filename = $image->store('public/images');
                    $imagename = $image->hashName();
                    $postImage->image = ($imagename);
                    $postImage->save();
                }
            }
            $post->update();
            ///$post->update($data);
            return $this->sendResponse('update post successfully', new PostResource($post));
        } else {
            return $this->sendError(401,'you are un authorised');
            //return $this->sendError('you are un authorised');
        }
    }

    public function destroy($id)
    {
        $user = auth()->user()->id;

        $post = Post::find($id);
        if (!$post) {
            return $this->sendError(404,'Invalid Post ID');
        }
        if ($user == $post->user_id) {
            $post->delete();
            return $this->sendResponse('Post deleted successfully',[]);
          //  return response()->json(['success' => 'Post deleted successfully']);
        } else {
            return $this->sendError(401,'you are un authorised');
        }
    }

    public function like(array $data)
    {
        $request = $data['post_id'];
        $post = Post::find($request);
        $like = Like::where("post_id", "like", "%$request%")
            ->where('user_id',Auth::user()->id)->first();
        if (!$post) {
            return $this->sendError(404 ,'There is no Post has this id');

        } elseif ($like) {
            $like->delete();
          /*  return response([
                'errorr' => '200',
                'message' => 'like has been delete ',
            ]);*/
            return $this->sendResponse('like has been delete ',[]);

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
            return $this->sendError(404 ,'There is no Post has this id');
        }
        $favourites = Favorite::create([
            'post_id' => $post->id,
            'user_id' => $user,
        ]);

        return $this->sendResponse('Post has been added to your favorites', $favourites);

    }
}
