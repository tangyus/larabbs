<?php

namespace App\Listeners;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use JPush\Client;

class PushNotification implements ShouldQueue
{
	protected $client;

    /*
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /*
     * Handle the event.
     *
     * @return void
     */
    public function handle(DatabaseNotification $notification)
    {
		// 本地环境默认不推送
        if (app()->environment('local')) {
        	return;
		}

		// 没有 registration_id 的不推送
		$user = $notification->notifiable();
        if (!$user->registration_id) {
			return;
		}

		$this->client->push()
			->setPlatform('all')
			->addRegistrationId($user->registration_id)
			->setNotificationAlert(strip_tags($notification->data['reply_content']))
			->send();
    }
}
