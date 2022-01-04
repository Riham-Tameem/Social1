<?php

namespace App\Http\Controllers\Api;
use App\Repositories\PostEloquent;
use Illuminate\Http\Request;

class HomeController extends BaseController
{

    public function __construct(PostEloquent $postEloquent)
    {
        $this->post= $postEloquent;
    }
    public function view(Request $request)
    {
        return $this->post->view($request->all());
    }

    ////////////****** LIKE SECTION *********///////////////
    public function like(Request $request)
    {
        return $this->post->like($request->all());
    }
}
