<?php namespace Codecycler\SURFconext\Classes\Extend;

use Event;
use RainLab\User\Models\User;
use Codecycler\SURFconext\Models\Token;

class RainLabUser
{
    protected $model;

    public function subscribe()
    {
        User::extend(function ($model) {
            $this->model = $model;

            $model->addDynamicMethod('createSurfConextToken', [$this, 'createSurfConextToken']);
        });
    }

    public function createSurfConextToken($user)
    {
        $data = [
            'user_id' => $this->model->id,
            'access_token' => $user->access_token,
            'organisation' => $user->organisation,
            'expired_at' => \Carbon\Carbon::now()->addSeconds($user->expiresIn - 10),
        ];

        return Token::create($data);
    }
}