<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
        $verifiData = \Cache::get($request->verification_key);

        if (!$verifiData) {
            return $this->response->error('验证码已失效', 422);
        }

        // 使用 hash_equals 比较字符串防止 时效攻击
        if (!hash_equals($verifiData['code'], $request->verification_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        User::create([
            'name' => $request->name,
            'phone' => $verifiData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除缓存验证码
        \Cache::forget($request->verification_key);

        return $this->response->created();
    }
}
