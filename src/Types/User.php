<?php

namespace Jakyeru\Larascord\Types;

class User
{
    /**
     * The user's id.
     */
    public string $id;

    /**
     * The user's username.
     */
    public string $username;

    /**
     * The user's discriminator.
     */
    public string $discriminator;

    /**
     * The user's avatar hash.
     */
    public ?string $avatar;

    /**
     * The user's email.
     */
    public ?string $email;

    /**
     * The user's email verification status.
     */
    public ?bool $verified;

    /**
     * The user's banner hash.
     */
    public ?string $banner;

    /**
     * The user's banner color.
     */
    public ?string $banner_color;

    /**
     * The user's accent color.
     */
    public ?string $accent_color;

    /**
     * The user's locale.
     */
    public string $locale;

    /**
     * The user's multifactor authentication status.
     */
    public bool $mfa_enabled;

    /**
     * The user's premium type.
     */
    public ?int $premium_type;

    /**
     * The user's public flags.
     */
    public ?int $public_flags;

}