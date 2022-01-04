<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Post\CreateRequest;

use App\Repositories\NotificationEloquent;
use App\Repositories\PostEloquent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends BaseController
{

    private $post;
    public function __construct(PostEloquent $postEloquent)
    {
        $this->post= $postEloquent;
    }
    public function index()
    {
        return $this->post->index();
    }

    public function create()
    {

    }

    public function store(CreateRequest $request)
    {
        return $this->post->store($request->all());

    }
    public function show($id)
    {
        return $this->post->show($id);
    }

    public function edit($id)
    {
        //
    }
    public function update(CreateRequest $request,$id)
    {
        return $this->post->update($request->all(), $id);
    }

    public function destroy($id)
    {
        return $this->post->destroy($id);
    }

    public function favourite(Request $request)
    {
        return $this->post->favourite($request->all());
    }
}
