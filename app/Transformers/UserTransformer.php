<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
	/**
	 * transform 是可以复用的，所有有关用户资源都可以通过 transform 返回，一些用户敏感信息请注意做处理
	 * @param User $user 用户模型
	 * @return array
	 */
	public function transform (User $user) {
		return [
			'id' => $user->id,
			'name' => $user->name,
			'email' => $user->email,
			'avatar' => $user->avatar,
			'introduction' => $user->introduction,
			'bound_phone' => $user->phone ? true : false,
			'bound_wechat' => ($user->weixin_openid || $user->weixin_unionid) ? true : false,
			'last_actived_at' => $user->last_actived_at->toDateTimeString(),
			'created_at' => $user->created_at->toDateTimeString(),
			'updated_at' => $user->updated_at->toDateTimeString(),
		];
	}
}