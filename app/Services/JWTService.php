<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{

    //! Private______________________________________________________________________________________________________
    private $payload;

    //! Public_______________________________________________________________________________________________________

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getJwtToken(): string
    {
        $jwt_token = JWT::encode($this->payload, env("JWT_SECRET"), 'HS256');
        return $jwt_token;
    }

    public static function checkTokenValidity(string $jwt_token): array
    {
        // $jwt_token = explode(" ", $jwt_token)[1];

        $decodedJWT = JWT::decode($jwt_token, new Key(env("JWT_SECRET"), 'HS256'));

        $JWTArr = (array) $decodedJWT;

        return $JWTArr;

    }

    public static function getTokenPayload(string $jwt_token): array {
        $bodyb64 = \explode('.', $jwt_token)[1];

        $payloadRaw = JWT::urlsafeB64Decode($bodyb64);

        $payload = JWT::jsonDecode($payloadRaw);

        return (array)$payload;
    }
}
