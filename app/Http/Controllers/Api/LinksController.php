<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Transformers\LinkTransformer;
use Illuminate\Http\Request;

class LinksController extends Controller
{
	/*
	 * 获取推荐资源列表
	 */
	public function index(Link $link)
	{
		$links = $link->getAllCached();

		return $this->response->collection($links, new LinkTransformer());
    }
}
