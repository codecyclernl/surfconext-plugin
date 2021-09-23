<?php namespace Codecycler\SURFconext\Classes\Contract;

interface JSONGetter
{
    public function get(string $url, array $params = [], array $options = []): array;
}
