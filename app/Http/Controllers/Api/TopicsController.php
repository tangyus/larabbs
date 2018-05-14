<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TopicRequest;
use App\Models\Topic;
use App\Models\User;
use App\Transformers\TopicTransformer;
use Illuminate\Http\Request;

class TopicsController extends Controller
{
	/*
	 * 获取帖子列表
	 * @param Request $request
	 * @param Topic $topic
	 */
	public function index(Request $request, Topic $topic)
	{
		$query = $topic->query();

		if ($categoryId = $request->category_id) {
			$query->where('category_id', $categoryId);
		}

		switch ($request->order) {
			case 'recent':
				$query->recent();
				break;
			default:
				$query->recentReplied();
				break;
		}
		$topics = $query->paginate(20);
		return $this->response->paginator($topics, new TopicTransformer());
	}

	/*
	 * 获取用户下的帖子
	 * @param User $user
	 * @param Request $request
	 */
	public function userIndex(User $user, Request $request)
	{
		$topics = $user->topics()->recent()->paginate(20);

		return $this->response->paginator($topics, new TopicTransformer());
	}

	/*
	 * 帖子详情
	 * @param Topic $topic
	 */
	public function show(Topic $topic)
	{
		return $this->response->item($topic, new TopicTransformer());
	}

	/*
	 * 创建帖子
	 * @param TopicRequest $request
	 * @param Topic $topic
	 */
	public function store(TopicRequest $request, Topic $topic)
	{
		$topic->fill($request->all());
		$topic->user_id = $this->user()->id;
		$topic->save();

		return $this->response->item($topic, new TopicTransformer())->setStatusCode(201);
    }

	/*
	 * 修改帖子
	 * @param TopicRequest $request
	 * @param Topic $topic
	 */
	public function update(TopicRequest $request, Topic $topic)
	{
		// 判断当前用户是否具有修改帖子的权限（帖子拥有者或具有管理权限）
		$this->authorize('update', $topic);

		$topic->update($request->all());
		return $this->response->item($topic, new TopicTransformer());
    }

	/*
	 * 删除帖子
	 * @param Topic $topic
	 */
	public function destroy(Topic $topic)
	{
		$this->authorize('update', $topic);

		$topic->delete();
		return $this->response->noContent();
    }
}
