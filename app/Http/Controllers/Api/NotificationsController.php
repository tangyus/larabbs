<?php

namespace App\Http\Controllers\Api;

use App\Transformers\NotificationTransformer;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
	/*
	 * 用户的通知列表
	 */
	public function index()
	{
		// notifications laravel消息通知系统提供，按创建时间倒序
		$notifications = $this->user()->notifications()->paginate(20);

		return $this->response->paginator($notifications, new NotificationTransformer());
	}

	/*
	 * 统计用户未读通知
	 */
	public function stats()
	{
		return $this->response->array([
			'unread_count' => $this->user()->notification_count,
		]);
	}

	/*
	 * 标记通知为已读
	 */
	public function read()
	{
		$this->user()->markAsRead();

		return $this->response->noContent();
	}
}
