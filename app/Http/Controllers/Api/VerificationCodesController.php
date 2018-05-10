<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
	public function store(VerificationCodeRequest $request, EasySms $easySms)
	{
		$phone = $request->phone;

		if (!app()->environment('production')) {
			$code = '1234';
		} else {
			// 生成4位随机数，左侧补0
			$code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

			try {
				$easySms->send($phone, [
					'content' => "【新鱼塘】您的验证码是{$code}。如非本人操作，请忽略本短信",
				]);
			} catch (ClientException $exception) {
				$response = $exception->getResponse();

				$result = json_decode($response->getBody()->getContents(), true);
				return $this->response->errorInternal($result['msg'] ?? '短信发送异常');
			}
		}

		$key = 'verificationCode_' . str_random(15);
		$expireAt = now()->addMinute(10);

		\Cache::put($key, ['phone' => $phone, 'code' => $code], $expireAt);

    	return $this->response->array([
    		'key' => $key,
			'expire_at' => $expireAt->toDateTimeString()
		])->setStatusCode(201);
	}
}
