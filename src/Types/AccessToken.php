<?php

namespace Jakyeru\Larascord\Types;

class AccessToken
{
    /**
     * The access token.
     */
    public string $access_token;

    /**
     * The type of token.
     */
    public string $token_type;

    /**
     * The time in seconds when the token expires.
     */
    public int $expires_in;

    /**
     * The Carbon instance when the token expires.
     */
    public \Carbon\Carbon $expires_at;

    /**
     * The refresh token.
     */
    public string $refresh_token;

    /**
     * The scopes the token has access to.
     */
    public string $scope;

    /**
     * AccessToken constructor.
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
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Returns true if the token is not expired.
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Returns true if the token has the given scope.
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, explode(' ', $this->scope));
    }

    /**
     * Returns true if the token has the given scopes.
     */
    public function hasScopes(array $scopes): bool
    {
        return array_diff($scopes, explode(' ', $this->scope)) === [];
    }

    /**
     * Converts the access token to a string.
     */
    public function __toString(): string
    {
        return $this->access_token;
    }
}