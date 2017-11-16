<?php
	namespace App\Handlers;

	use Intervention\Image\Facades\Image;

	class ImageUploadHandler
	{
		// 允许上传的图片扩展名
		protected $allowed_ext = ['png', 'jpg', 'jpeg', 'gif'];

		/**
		 * 文件上传
		 * @param $file 文件上传的名称
		 * @param $folder 文件归属的类型（用户头像、帖子图片...）
		 * @param $file_prefix 所属数据模型ID
		 * @param bool $max_width 若需要裁剪，最大宽度
		 * @return array|bool
		 */
		public function save($file, $folder, $file_prefix, $max_width = false)
		{
			// 存储图片上传的文件规则，如uploads/images/avatars/201709/21/
			$folder_name = "uploads/images/$folder/" . date('Ym', time()) . '/' . date('d', time()) . '/';

			// 图片存储的物理路径，public目录下...
			$upload_path = public_path() . '/' . $folder_name;

			// 获取上传图片的扩展名，若没有则默认png
			$extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

			// 文件名称，$file_prefix为相关数据模型的ID，如1_1493521050_7BVc9v9ujP.png
			$filename = $file_prefix . '_' . time() . '_' . str_random(10) . '.' . $extension;

			// 如果上传的图片类型不是允许上传类型中的，则默认上传失败
			if ( ! in_array($extension, $this->allowed_ext)) {
				return false;
			}

			// 将图片移动到我们的目标存储路径中
			$file->move($upload_path, $filename);

			if ($max_width && $extension != 'gif') {
				// 裁剪图片
				$this->reduceSize($upload_path . $filename, $max_width);
			}

			// 返回文件路径
			return ['path' => config('app.url') . "/$folder_name/$filename"];
		}

		/**
		 * 裁剪图片
		 * @param $file_path 文件路径
		 * @param $max_width 最大宽度
		 */
		public function reduceSize($file_path, $max_width)
		{
			// 先实例化，传参是文件的磁盘物理路径
			$image = Image::make($file_path);

			// 进行大小调整的操作
			$image->resize($max_width, null, function ($constraint) {

				// 设定宽度是 $max_width，高度等比例双方缩放
				$constraint->aspectRatio();

				// 防止裁图时图片尺寸变大
				$constraint->upsize();
			});

			// 对图片修改后进行保存
			$image->save();
		}
	}