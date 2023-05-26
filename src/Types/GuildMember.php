<?php

namespace Jakyeru\Larascord\Types;

class GuildMember
{
    /*
     * The user's guild avatar hash.
     */
    public ?string $avatar;

    /*
     * When the user joined the guild.
     */
    public \Carbon\Carbon $joined_at;

    /*
     * The user's guild nickname.
     */
    public ?string $nick;

    /*
     * Array of role ids.
     */
    public array $roles;

    /*
     * Whether the user is deafened in voice channels.
     */
    public bool $deaf;

    /*
     * Whether the user is muted in voice channels.
     */
    public bool $mute;

    /*
     * GuildMember constructor.
     */
    public function __construct(object $data) {
        $this->avatar = $data->avatar;
        $this->joined_at = \Carbon\Carbon::parse($data->joined_at);
        $this->nick = $data->nick;
        $this->roles = $data->roles;
        $this->deaf = $data->deaf;
        $this->mute = $data->mute;
    }
}