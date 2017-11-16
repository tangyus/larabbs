<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['show']]);
	}
	/**
	 * 查看用户信息
	 * @param User $user
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

	/**
	 * 编辑用户
	 * @param User $user
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function edit(User $user)
    {
		$this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

	/**
	 * 更新信息
	 * @param UserRequest $request
	 * @param ImageUploadHandler $uploader
	 * @param User $user
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user)
    {
		$this->authorize('update', $user);
		$data = $request->all();

		// 处理用户上传头像
		if ($request->avatar) {
			$result = $uploader->save($request->avatar, 'avatars', $user->id, 362);
			if ($result) {
				$data['avatar'] = $result['path'];
			}
		}

        $user->update($data);
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
    }
}
