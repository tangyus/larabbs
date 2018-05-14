<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReplyRequest;
use App\Models\Reply;
use App\Models\Topic;
use App\Models\User;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
	/*
	 * 获取帖子的回复列表
	 */
	public function index(Topic $topic)
	{
		$replies = $topic->replies()->paginate(20);

		return $this->response->paginator($replies, new ReplyTransformer());
	}

	/*
	 * 获取用户发表的回复列表
	 */
	public function userIndex(User $user)
	{
		$replies = $user->replies()->paginate(20);

		return $this->response->paginator($replies, new ReplyTransformer());
	}

	/*
	 * 话题回复
	 */
	public function store(ReplyRequest $request, Reply $reply, Topic $topic)
	{
		$reply->content = $request->content;
		$reply->topic_id = $topic->id;
		$reply->user_id = $this->user()->id;
		$reply->save();

		return $this->response->item($reply, new ReplyTransformer())->setStatusCode(201);
    }

    /*
     * 删除回复
     */
	public function destroy(Topic $topic, Reply $reply)
	{
		if ($reply->topic_id != $topic->id) {
			return $this->response->errorBadRequest();
		}

		$this->authorize('destroy', $reply);
		$reply->delete();

		return $this->response->noContent();
    }
}
