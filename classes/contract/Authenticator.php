<?php namespace Codecycler\SURFconext\Classes\Contract;

use Lcobucci\JWT\Token\DataSet;

interface Authenticator
{
    public function authUser(DataSet $claims);
}
