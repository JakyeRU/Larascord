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

    /**
     * User constructor.
     */
    public function __construct(object $data)
    {
        $this->id = $data->id;
        $this->username = $data->username;
        $this->discriminator = $data->discriminator;
        $this->avatar = $data->avatar ?? NULL;
        $this->email = $data->email ?? NULL;
        $this->verified = $data->verified ?? FALSE;
        $this->banner = $data->banner ?? NULL;
        $this->banner_color = $data->banner_color ?? NULL;
        $this->accent_color = $data->accent_color ?? NULL;
        $this->locale = $data->locale;
        $this->mfa_enabled = $data->mfa_enabled;
        $this->premium_type = $data->premium_type ?? NULL;
        $this->public_flags = $data->public_flags ?? NULL;    }
}