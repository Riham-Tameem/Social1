<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'id' => $this->id,
            'text' => $this->text,
            'image'=> $this->images,
            'date'=>$this->date,
            'user' => $this->user,
           // 'comments'=>$this->comments,
            'comments' =>  CommentResource::collection($this->comments),
            'share' =>$this->share,

        ];
    }
}
