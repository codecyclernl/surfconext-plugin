<?php namespace Codecycler\SURFconext\Classes\Contract;

interface JSONPoster
{
    public function post(string $url, array $params = [], $body = null, array $options = []): array;
}
