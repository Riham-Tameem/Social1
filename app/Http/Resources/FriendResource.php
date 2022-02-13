<?php

namespace App\Http\Resources;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
  return [
      "id"=> $this->id,
        "name"=> $this->name,
        "email"=> $this->email,
        "image"=>$this->image
  ];
        //return $this->friend_id;

//       $friend=User::where('id',$this->friend_id)->get();
//       return [
//
//            'id' =>   $this->id,
//           //'friends'=> $this->users
//          'friends' =>  UserResource::collection($friend),
//          // 'friends'=>$this->friend_id
//            ];

    }
}
