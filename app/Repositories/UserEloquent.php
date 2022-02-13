<?php

namespace App\Repositories;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\FriendResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\FcmToken;
use App\Models\Friend;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class UserEloquent extends BaseController
{
    private $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function register(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        \request()->request->add(['username'=>$data['email']]);
        return $this->login();

    }

    public function login()
    {
        $proxy = Request::create('oauth/token', 'POST');
        $response = Route::dispatch($proxy);
        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getContent());
       // dd($response);
        if ($statusCode != 200){
           // dd('fff');
           return $this->sendError(401,$response->message);
            //return response()->json(['status' => false, 'statusCode' => 401, 'message' => $response->message,'data'=> [] ]);

        }
        $response_token = $response;
        $token = $response->access_token;
        \request()->headers->set('Authorization', 'Bearer ' . $token);

        $proxy = Request::create('api/getAuthUser', 'GET');
        $response = Route::dispatch($proxy);
        // dd($response);
        $statusCode = $response->getStatusCode();
        //dd(json_decode($response->getContent()));
        //  dd($response->getContent());
        $user = json_decode($response->getContent())->data;


        if (isset($user)) {
            // create fcm token

            $data = \request()->all();
            $data['user_id'] = $user->id;
            if (isset($data['fcm_token'])) {

                $fcmToken = FcmToken::where('device_type', $data['device_type'])->where('user_id', $user->id)->where('device_id', $data['device_id'])->first();
                if (!isset($fcmToken))
                    FcmToken::create($data);
                else{
                    $fcmToken->fcm_token = $data['fcm_token'] ;
                    $fcmToken->save();
                }
            }
        }

        return $this->sendResponse('Successfully Login', ['token' => $response_token, 'user' => $user]);
    }

    public function addFriend(array $data)
    {
        // dd($id);
        $user = auth()->user()->id;
        //dd($user);
        $friend_ids = $data['friend_id'];
        //dd($friend_ids);
        $myFriend = Friend::where('user_id', $user)->pluck('friend_id')->toArray();
        // $addUser=auth()->user()->friends()->attach([$id]);
        if (in_array($friend_ids, $myFriend)) {
            /*return response(['success' => '200',
                'message' => 'friend already exists',
            ]);*/
            return $this->sendResponse('friend already exists  ', []);

        }
        $is_exists=User::where('id',$data['friend_id'])->first();
        if($is_exists){
            $addFriend = Friend::create([
                'friend_id' => $data['friend_id'],
                'user_id' => $user,
            ]);
            return $this->sendResponse('add friend Successfully ', new FriendResource($addFriend));
        }
        return $this->sendError(404, 'no user with this id');

    }

    public function logout(array $data)
    {
        $user = Auth::user()->token();
        $user->revoke();
        return $this->sendResponse('user has been log out successfully', []);

      //  return response(['message' => 'user has been log out successfully '], 200);
    }

    public function viewProfile(array $data)
    {
        $user = auth()->user()->id;
        if ($data['id'] == $user) {
            //$my_posts = Post::where('user_id', $user)->pluck('id')->toArray();
            // $posts = Post::whereIn('id', $my_posts)->get();
            $my_account = User::find($user);
            return $this->sendResponse('viewProfile', new UserResource($my_account));
        } else {
            $user_info = User::find($data['id']);
            if (!$user_info) {
                return $this->sendError(404,'There is no User has this id');
                //return $this->sendError();
            }
            return $this->sendResponse('user info', new UserResource($user_info));
        }
    }

    public function getUser(array $data)
    {
        $user_id = $data['user_id'] ?? auth()->user()->id;
        $user = User::find($user_id);
        //return $this->sendResponse('user info', new UserResource($user));
        return $this->sendResponse('user info', $user);

    }

    public function getAuthUser()
    {
        $user = auth()->user();
        ///$user = User::find($user_id);
        //return $this->sendResponse('user info', new UserResource($user));
        return $this->sendResponse('user info', $user);

    }

    public function friendlist()
    {

//        $user = auth()->user()->id;
//        // dd($user);
//        $friends = Friend::where('user_id', $user)->get();
//     //  dd($friends);
//        $my_friend_list=[];
//        if ($friends) {
//        foreach ($friends as $friend){
//            $friend_list = Friend::where('friend_id', $friend->friend_id)->get();
//            $my_friend_list []=$friend_list;
//        }
////   dd($my_friend_list);
//            return $this->sendResponse('Success',FriendResource::collection($my_friend_list));
//        }
//        return $this->sendError(404, 'no friends');
//
//       // return $this->sendError('no friends');


            return $this->sendResponse('Success',FriendResource::collection(\auth()->user()->friends));

    }

    public function removefriend(array $data)
    {
        $validator = Validator::make($data, [
            'friend_id' => "required"
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $friend_id = $data['friend_id'];
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $friend_id)
            ->first();
        if($friend){
            $friend->delete();
            return $this->sendResponse('friend deleted successfully', []);

        }
       // return response()->json(['success' => 'friend deleted successfully']);

    }

    function changePassword(array $data)
    {
        $validator = Validator::make($data, [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError( 422,   $validator->errors()->all());
            // return response(['errors' => $validator->errors()->all()], 422);
        }
        $user_id = auth()->user()->id;
        if ((Hash::check(request('old_password'), auth()->user()->password)) == false) {
            $message = "in correct old password";
            return $this->sendError(422, $message);
        } else {
            //User::where('id', $user_id)->update(['password' => Hash::make($input['new_password'])]);
            User::where('id', $user_id)->update(['password' => bcrypt($data['new_password'])]);
            $message = "Success change password";
        }
        return $this->sendResponse($message, []);
     //   return response()->json(['status' => true, 'statusCode' => 200, 'message' => $message]);

    }

    public function editUser(array $data)
    {
        $user = Auth::user();
        /*$user->name = $data['name'];
        $user->email = $data['email'];*/
        if (isset($data['image'] )) {
            // $image = $request->file('image');
            $filename = $data['image']->store('images');
//            $imagename = $data['image']->hashName();
            $data['image'] = $filename;
            $user->image = $data['image'];
        }
        $user->update($data);
        return $this->sendResponse('Success editUser', $user);
    }

    public function search(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError(422, $validator->errors()->all());
          //  return response(['errors' => $validator->errors()->all()], 422);
        }
        $name = $data['name'];
        $user = User::where('name', $name)->first();
        //  dd($user);
        return $this->sendResponse('user info', new UserResource($user));
    }
}
