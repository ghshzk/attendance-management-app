<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Fortify;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\RegisterResponse;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request,
                          CreatesNewUsers $creator): RegisterResponse
    {
        if (config('fortify.lowercase_usernames')) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        $validated = $request->validated();

        event(new Registered($user = $creator->create($validated)));

        //未認証ユーザー情報をセッションに保存
        session(['unauthenticated_user' => $user]);

        return app(RegisterResponse::class);
    }
}
