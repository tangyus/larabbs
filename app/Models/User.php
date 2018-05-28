<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
	use HasRoles, Traits\ActiveUserHelper, Traits\LastActivedAtHelper;
    use Notifiable {
		notify as protected laravelNotify;
	}

	// JWT 必须实现的两个方法 getJWTIdentifier getJWTCustomClaims
	public function getJWTIdentifier()
	{
		// 返回 user id
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		// 返回额外JWT 荷载中的内容
		return [];
	}

	public function notify($instance)
	{
		// 如果要通知的人是当前用户，就不必通知了！
		if ($this->id == Auth::id()) {
			return;
		}
		$this->increment('notification_count');
		$this->laravelNotify($instance);
	}

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'email', 'password', 'introduction', 'avatar',
		'weixin_openid', 'weixin_unionid', 'registration_id', 'weixin_session_key',
		'weapp_openid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

	public function topics()
	{
		return $this->hasMany(Topic::class);
	}

	public function replies()
	{
		return $this->hasMany(Reply::class);
	}

	public function scopeRecent($query)
	{
		return $query->orderBy('id', 'desc');
	}

	// 判断用户权限
	public function isAuthorOf($model)
	{
		return $this->id == $model->user_id;
	}

	// 清除未读消息
	public function markAsRead()
	{
		$this->notification_count = 0;
		$this->save();
		$this->unreadNotifications->markAsRead();
	}

	/**
	 * 在后台编辑用户时，数据入库前，运用 Eloquent 修改器 对修改的密码进行处理
	 * @param $value
	 */
	public function setPasswordAttribute($value)
	{
		// 如果值的长度等于 60，即认为是已经做过加密的情况
		if (strlen($value) != 60) {

			// 不等于 60，做密码加密处理
			$value = bcrypt($value);
		}

		$this->attributes['password'] = $value;
	}

	/**
	 * 在后台编辑用户时，数据入库前，运用 Eloquent 修改器 对修改的头像路径进行处理
	 * @param $path
	 */
	public function setAvatarAttribute($path)
	{
		// 如果不是 `http` 子串开头，那就是从后台上传的，需要补全 URL
		if ( ! starts_with($path, 'http')) {

			// 拼接完整的 URL
			$path = config('app.url') . "/uploads/images/avatars/$path";
		}

		$this->attributes['avatar'] = $path;
	}
}
