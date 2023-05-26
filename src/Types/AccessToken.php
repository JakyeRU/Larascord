<?php

namespace Jakyeru\Larascord\Types;

class AccessToken
{
    /**
     * The access token.
     * @var string $access_token
     */
    public string $access_token;

    /**
     * The type of token.
     * @var string $token_type
     */
    public string $token_type;

    /**
     * The time in seconds when the token expires.
     * @var int $expires_in
     */
    public int $expires_in;

    /**
     * The Carbon instance when the token expires.
     */
    public \Carbon\Carbon $expires_at;

    /**
     * The refresh token.
     * @var string $refresh_token
     */
    public string $refresh_token;

    /**
     * The scopes the token has access to.
     * @var string $scope
     */
    public string $scope;

    /**
     * AccessToken constructor.
     * @param object $data
     */
    public function __construct(object $data)
    {
        $this->access_token = $data->access_token;
        $this->token_type = $data->token_type;
        $this->expires_in = $data->expires_in;
        $this->expires_at = \Carbon\Carbon::now()->addSeconds($data->expires_in);
        $this->refresh_token = $data->refresh_token;
        $this->scope = $data->scope;
    }

    /**
     * Returns true if the token is expired.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Returns true if the token is not expired.
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }
}