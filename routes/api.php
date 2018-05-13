<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' => ['serializer:array', 'bindings']
], function ($api) {
	// 注册登录相关路由 限制频率配置 sign
	$api->group([
		'middleware' => 'api.throttle',
		'limit' => config('api.rate_limits.sign.limit'),
		'expires' => config('api.rate_limits.sign.expires'),
	], function ($api) {
		// 短信验证码
		$api->post('verificationCodes', 'VerificationCodesController@store')
			->name('api.verificationCodes.store');

		// 用户注册
		$api->post('users', 'UsersController@store')
			->name('api.users.store');

		// 图片验证码
		$api->post('captchas', 'CaptchasController@store')
			->name('api.captchas.store');

		// 第三方登录
		$api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
			->name('api.socials.authorizations.store');

		// 登录
		$api->post('authorizations', 'AuthorizationsController@store')
			->name('api.authorizations.store');

		// 刷新 token
		$api->put('authorizations/current', 'AuthorizationsController@update')
			->name('api.authorizations.update');

		// 删除 token
		$api->delete('authorizations/current', 'AuthorizationsController@destroy')
			->name('api.authorizations.destroy');
	});

	// 访问相关路由，限制频率配置 access
	$api->group([
		'middleware' => 'api.throttle',
		'limit' => config('api.rate_limits.access.limit'),
		'expires' => config('api.rate_limits.access.expires'),
	], function ($api) {
		// 不需要 token 的接口，游客可访问
		$api->get('categories', 'CategoriesController@index')
			->name('api.categories.index');
		// 获取帖子列表
		$api->get('topics', 'TopicsController@index')
			->name('api.topics.index');
		// 帖子详情
		$api->get('topics/{topic}', 'TopicsController@show')
			->name('api.topics.show');
		// 获取用户下的帖子
		$api->get('users/{user}/topics', 'TopicsController@userIndex')
			->name('api.users.topics.index');

		// 需要验证 token 的接口
		$api->group(['middleware' => 'api.auth'], function ($api) {
			// 当前登录用户信息
			$api->get('user', 'UsersController@me')
				->name('api.user.show');
			// 上传图片
			$api->post('images', 'ImagesController@store')
				->name('api.images.store');
			// 修改用户信息
			$api->patch('user', 'UsersController@update')
				->name('api.user.update');
			// 发布帖子
			$api->post('topics', 'TopicsController@store')
				->name('api.topics.store');
			// 修改帖子
			$api->patch('topics/{topic}', 'TopicsController@update')
				->name('api.topics.update');
			// 删除帖子
			$api->delete('topics/{topic}', 'TopicsController@destroy')
				->name('api.topics.destroy');
		});
	});
});

$api->version('v2', function ($api) {
    $api->get('version', function () {
        return response('this is version v2');
    });
});