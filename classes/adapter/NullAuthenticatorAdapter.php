<?php namespace Codecycler\SURFconext\Classes\Adapter;

use Lcobucci\JWT\Token\DataSet;
use Codecycler\SURFconext\Classes\Contract\Authenticator;

class NullAuthenticatorAdapter implements Authenticator
{
    public function authUser(DataSet $claims)
    {
    }
}
