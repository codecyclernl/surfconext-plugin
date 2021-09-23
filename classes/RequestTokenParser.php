<?php namespace Codecycler\SURFconext\Classes;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Parser;
use Illuminate\Http\Request;
use Codecycler\SURFconext\Classes\Exception\AuthenticationException;

class RequestTokenParser
{
    const AUTH_HEADER = "Authorization";

    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Request $request): Token
    {
        $bearer = $request->headers->get(static::AUTH_HEADER);

        if (empty($bearer)) {
            throw new AuthenticationException("Request doesn't contain auth token");
        }

        $parts = explode(" ", $bearer);

        if (count($parts) < 2) {
            throw new AuthenticationException("Invalid format of auth header");
        }

        $jwt = $parts[1];

        return $this->parser->parse($jwt);
    }
}
