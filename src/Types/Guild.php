<?php

namespace Jakyeru\Larascord\Types;

class Guild
{
    /*
     * The guild id.
     */
    public string $id;

    /*
     * The guild name.
     */
    public string $name;

    /*
     * The guild icon hash.
     */
    public ?string $icon;

    /*
     * Whether the user is the owner of the guild.
     */
    public bool $owner;

    /**
     * The permissions of the user in the guild.
     *
     * @deprecated Use $permissions_new instead.
     */
    public int $permissions;

    /*
     * The guild features.
     */
    public array $features;

    /*
     * The permissions of the user in the guild.
     */
    public string $permissions_new;

    /*
     * Guild constructor.
     */
    public function __construct(object $data)
    {
        $this->id = $data->id;
        $this->name = $data->name;
        $this->icon = $data->icon;
        $this->owner = $data->owner;
        $this->permissions = $data->permissions;
        $this->features = $data->features;
        $this->permissions_new = $data->permissions_new;
    }
}