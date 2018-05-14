<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\Image;
use App\Models\User;
use App\Transformers\UserTransformer;
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

		$user = User::create([
            'name' => $request->name,
            'phone' => $verifiData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除缓存验证码
        \Cache::forget($request->verification_key);

        return $this->response->item($user, new UserTransformer())
			->setMeta([
				'access_token' => \Auth::guard('api')->fromUser($user),
				'token_type' => 'Bearer',
				'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60,
			])
			->setStatusCode(201);
    }

	/**
	 * 获取当前登录用户信息
	 * @return \Dingo\Api\Http\Response
	 */
	public function me()
	{
		// Dingo\Api\Routing\Helpers traits提供的 user 方法，获取当前登录的用户信息，
		// $this->user() 等同于 \Auth::guard('api')->user()
		return $this->response->item($this->user(), new UserTransformer());
    }

	/*
	 * 修改用户信息
	 * @param UserRequest $request
	 */
	public function update(UserRequest $request)
	{
		$user = $this->user();

		$attributes = $request->only(['name', 'email', 'introduction', 'registration_id']);

		if ($request->avatar_image_id) {
			$image = Image::find($request->avatar_image_id);

			$attributes['avatar'] = $image->path;
		}
		$user->update($attributes);

		return $this->response->item($user, new UserTransformer());
    }

	public function activedIndex(User $user)
	{
		return $this->response->collection($user->getActiveUsers(), new UserTransformer());
    }
}
