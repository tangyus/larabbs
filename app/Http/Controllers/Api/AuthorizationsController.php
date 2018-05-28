<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Http\Requests\Api\WeappAuthorizationRequest;
use App\Models\User;
use Auth;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;

class AuthorizationsController extends Controller
{
	/**
	 * 社会化登录
	 * @param $type
	 * @param SocialAuthorizationRequest $request
	 */
	public function socialStore($type, SocialAuthorizationRequest $request)
	{
		if (!in_array($type, ['weixin'])) {
			return $this->response->errorBadRequest();
		}

		$driver = \Socialite::driver($type);

		try {
			if ($code = $request->code) {
				$response = $driver->getAccessTokenResponse($code);
				$token = array_get($response, 'access_token');
			} else {
				$token = $request->access_token;

				if ($type == 'weixin') {
					$driver->setOpenId($request->openid);
				}
			}

			$oauthUser = $driver->userFromToken($token);
		} catch (\Exception $exception) {
			return $this->response->errorUnauthorized('参数错误，未获取用户信息');
		}

		switch ($type) {
			case 'weixin':
				$unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
				if ($unionid) {
					$user = User::where('weixin_unionid', $unionid)->first();
				} else {
					$user = User::where('weixin_openid', $oauthUser->getId())->first();
				}

				// 没有用户，创建一个用户
				if (!$user) {
					$user = User::create([
						'name' => $oauthUser->getNickname(),
						'avatar' => $oauthUser->getAvatar(),
						'weixin_openid' => $oauthUser->getId(),
						'weixin_unionid' => $unionid
					]);
				}
				break;
		}

		$token = Auth::guard('api')->fromUser($user);
		return $this->respondWithToken($token)->setStatusCode(201);
    }

	/**
	 * 用户登录 api授权
	 * @param AuthorizationRequest $request
	 */
	public function store(AuthorizationRequest $request)
	{
		$username = $request->username;

		filter_var($username, FILTER_VALIDATE_EMAIL) ?
			$credentials['email'] = $username :
			$credentials['phone'] = $username;

		$credentials['password'] = $request->password;

		if (!$token = Auth::guard('api')->attempt($credentials)) {
			return $this->response->errorUnauthorized(trans('auth.failed'));
		}

		return $this->respondWithToken($token)->setStatusCode(201);
    }

	/**
	 * 刷新 token
	 * @return \Dingo\Api\Http\Response
	 */
	public function update()
	{
		$token = Auth::guard('api')->refresh();

		return $this->respondWithToken($token);
    }

	/**
	 * 删除 token
	 * @return \Dingo\Api\Http\Response
	 */
	public function destroy()
	{
		Auth::guard('api')->logout();

		return $this->response->noContent();
    }

    protected function respondWithToken($token) {
		return $this->response->array([
			'access_token' => $token,
			'token_type' => 'Bearer',
			'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
		]);
	}

	public function weappStore(WeappAuthorizationRequest $request)
	{
		$code = $request->code;

		// 根据 code 获取微信 openID 和 session_key
		$miniPro = \EasyWeChat::miniProgram();
		$data = $miniPro->auth->session($code);

		// 如果结果错误，说明 code 已过期或不正确，返回 401 错误
		if (isset($data['errcode'])) {
			return $this->response->errorUnauthorized('code 不正确');
		}

		// 找到 openID 对应的用户
		$user = User::where('weapp_openid', $data['openid'])->first();
		$attributes['weixin_session_key'] = $data['session_key'];

		// 未找到对应的用户，则需要提交用户名密码，进行 weapp_openid 绑定
		if (!$user) {
			// 未提交用户名，返回 403 错误
			if (!$request->username) {
				return $this->response->errorForbidden('用户不存在');
			}

			$username = $request->username;

			// 用户名可以是邮箱或电话
			filter_var($username, FILTER_VALIDATE_EMAIL) ?
				$credentials['email'] = $username :
				$credentials['phone'] = $username;
			$credentials['password'] = $request->password;

			// 验证用户名和密码是否正确
			if (!Auth::guard('api')->once($credentials)) {
				return $this->response->errorUnauthorized('用户名或密码错误');
			}

			// 获取对应的用户
			$user = Auth::guard('api')->getUser();
			$attributes['weapp_openid'] = $data['openid'];
		}

		$user->update($attributes);

		// 创建 JWT token 认证
		$token = Auth::guard('api')->fromUser($user);
		return $this->respondWithToken($token)->setStatusCode(201);
	}
}
