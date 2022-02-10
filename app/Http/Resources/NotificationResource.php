<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $user=User::where('id',$this->sender_id)->get();
        $date= Carbon::parse($this->created_at)->diffForHumans();
       // return parent::toArray($request);
        return [
            'id' => $this->id,
            'action' => $this->action,
            'date' =>$date,
            'user' => UserResource::collection($user),
        ];
    }
}
