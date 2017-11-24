<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Topic;

class CategoriesController extends Controller
{
    /**
     * 话题分类展示
     * @param Request $request
     * @param Category $category
     * @param Topic $topic
     * @param User $user
     * @param Link $link
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public function show(Request $request, Category $category, Topic $topic, User $user, Link $link)
	{
		// 读取分类 ID 关联的话题，并按每 20 条分页
		$topics = $topic->withOrder($request->order)
						->where('category_id', $category->id)
						->paginate();

        // 活跃用户列表
        $active_users = $user->getActiveUsers();

        // 资源链接
        $links = $link->getAllCached();

		// 传参变量话题和分类到模板中
		return view('topics.index', compact('topics', 'category', 'active_users', 'links'));
	}
}
