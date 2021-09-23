<?php namespace Codecycler\SURFconext\Classes;

use October\Rain\Database\QueryBuilder;

class TokenStorage
{
    private $query;

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function saveRefresh(string $sub, string $iss, string $refreshToken): bool
    {
        return $this->query->getConnection()
            ->table("codecycler_surfconext_tokens")
            ->updateOrInsert([
                'sub' => $sub,
                'iss' => $iss,
            ], [
                'refresh_token' => $refreshToken,
            ]);
    }

    public function fetchRefresh(string $sub, string $iss): ?string
    {
        $list = $this->query->getConnection()
            ->table("codecycler_surfconext_tokens")
            ->select(["refresh_tokens"])
            ->where('sub', $sub)
            ->where('iss', $iss)
            ->limit(1)
            ->get();

        if ($list->isEmpty()) {
            return null;
        }

        return $list->first()->refresh_token;
    }
}
