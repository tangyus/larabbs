<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
	public function root()
	{
		return view('pages.root');
    }

	/**
	 * 权限拒绝时，跳转路由
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function permissionDenied()
	{
		// 如果当前用户有权限访问后台，直接跳转访问
		if (config('administrator.permission')()) {
			return redirect(url(config('administrator.uri')), 302);
		}
        // 否则使用视图
        return view('pages.permission_denied');
    }
}
