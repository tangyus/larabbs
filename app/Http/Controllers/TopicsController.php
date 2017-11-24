<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Illuminate\Support\Facades\Auth;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	/**
	 * 话题首页
	 * @param Request $request
	 * @param Topic $topic
	 * @param User $user
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index(Request $request, Topic $topic, User $user)
	{
		$topics = $topic->withOrder($request->order)->paginate();
        $active_users = $user->getActiveUsers();

		return view('topics.index', compact('topics', 'active_users'));
	}

	/**
	 * 查看话题
	 * @param Topic $topic
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function show(Request $request, Topic $topic)
    {
		// URL 矫正
		if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
			return redirect($topic->link(), 301);
		}

        return view('topics.show', compact('topic'));
    }

	/**
	 * 新建话题
	 * @param Topic $topic
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create(Topic $topic)
	{
		$categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	/**
	 * 保存话题
	 * @param TopicRequest $request
	 * @param Topic $topic
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(TopicRequest $request, Topic $topic)
	{
		$topic->fill($request->all());
		$topic->user_id = Auth::id();
		$topic->save();

		return redirect()->to($topic->link())->with('success', '创建话题成功！');
	}

	/**
	 * 编辑话题
	 * @param Topic $topic
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
		$categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	/**
	 * 更新话题
	 * @param TopicRequest $request
	 * @param Topic $topic
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->to($topic->link())->with('success', '修改话题成功！');
	}

	/**
	 * 删除话题
	 * @param Topic $topic
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', '删除话题成功!');
	}

	/**
	 * 帖子图片上传
	 * @param Request $request
	 * @param ImageUploadHandler $uploader
	 * @return array
	 */
	public function uploadImage(Request $request, ImageUploadHandler $uploader)
	{
		// 初始化返回数据，默认是失败的
		$data = [
			'success'   => false,
			'msg'       => '上传失败!',
			'file_path' => ''
		];
		// 判断是否有上传文件，并赋值给 $file
		if ($file = $request->upload_file) {
			// 保存图片到本地
			$result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
			// 图片保存成功的话
			if ($result) {
				$data['file_path'] = $result['path'];
				$data['msg']       = "上传成功!";
				$data['success']   = true;
			}
		}
		return $data;
	}
}