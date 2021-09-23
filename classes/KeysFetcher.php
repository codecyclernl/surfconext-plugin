<?php namespace Codecycler\SURFconext\Classes;

use Carbon\Carbon;
use Lcobucci\JWT\Decoder;
use Lcobucci\JWT\Signer\Key\InMemory as Key;
use Codecycler\SURFconext\Classes\Contract\JSONGetter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class KeysFetcher
{
    private JSONGetter $fetcher;

    private string $jwksURI;

    private Decoder $decoder;

    private CacheRepository $cache;

    public function __construct(JSONGetter $fetcher, CacheRepository $cache, Decoder $decoder, string $jwksURI)
    {
        $this->fetcher = $fetcher;
        $this->cache = $cache;
        $this->jwksURI = $jwksURI;
        $this->decoder = $decoder;
    }

    public function getByKID(string $kid): ?Key
    {
        $cacheKey = 'keys.' . $kid;

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $keys = $this->fetch();

        if (!isset($keys[$kid])) {
            return null;
        }

        $this->cache->put($cacheKey, $keys[$kid], Carbon::now()->addHours(6));

        return $keys[$kid];
    }

    public function fetch(): array
    {
        $result = [];

        $data = $this->fetcher->get($this->jwksURI);
        foreach ($data['keys'] as $key) {
            $result[$key['kid']] = Key::plainText($this->createPemFromModulusAndExponent($key['n'], $key['e']));
        }

        return $result;
    }

    protected function createPemFromModulusAndExponent(string $n, string $e): string
    {
        $modulus = $this->decoder->base64UrlDecode($n);
        $publicExponent = $this->decoder->base64UrlDecode($e);

        $components = [
            'modulus' => pack('Ca*a*', 2, $this->encodeLength(strlen($modulus)), $modulus),
            'publicExponent' => pack('Ca*a*', 2, $this->encodeLength(strlen($publicExponent)), $publicExponent),
        ];

        $RSAPublicKey = pack(
            'Ca*a*a*',
            48,
            $this->encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
            $components['modulus'],
            $components['publicExponent']
        );

        $rsaOID = pack('H*', '300d06092a864886f70d0101010500');
        $RSAPublicKey = chr(0) . $RSAPublicKey;
        $RSAPublicKey = chr(3) . $this->encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;
        $RSAPublicKey = pack(
            'Ca*a*',
            48,
            $this->encodeLength(strlen($rsaOID . $RSAPublicKey)),
            $rsaOID . $RSAPublicKey
        );

        $RSAPublicKey = "-----BEGIN PUBLIC KEY-----" . PHP_EOL
            . chunk_split(base64_encode($RSAPublicKey), 64, PHP_EOL)
            . '-----END PUBLIC KEY-----';

        return $RSAPublicKey;
    }

    protected function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }
}
