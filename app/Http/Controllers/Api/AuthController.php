<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\EditRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Repositories\UserEloquent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
class AuthController extends BaseController
{
    public function __construct(UserEloquent $userEloquent)
    {
        $this->user= $userEloquent;
    }
    public function register(RegisterRequest $request)
    {
        return $this->user->register($request->all());
    }
    public function login()
    {
        return $this->user->login();
    }

    public function addFriend(Request $request)
    {
        return $this->user->addFriend($request->all());
    }
    public function logout(Request $request)
    {
        return $this->user->logout($request->all());
    }
    public function viewProfile(Request $request)
    {
        return $this->user->viewProfile($request->all());
    }

    public function getUser(Request $request)
    {
        return $this->user->getUser($request->all());

    }

    public function getAuthUser()
    {
        return $this->user->getAuthUser();

    }
    public function friendlist()
    {
        return $this->user->friendlist();
    }
    public function removefriend(Request $request)
    {
        return $this->user->removefriend($request->all());
    }
    public function changePassword(Request $request){
        return $this->user->changePassword($request->all());
    }
    public function editUser(EditRequest $request){
        return $this->user->editUser($request->all());
    }
    public function search(Request $request){
        return $this->user->search($request->all());
    }
}
