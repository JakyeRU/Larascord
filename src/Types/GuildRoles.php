<?php
namespace Jakyeru\Larascord\Types;

class GuildRoles
{
    /*
     * The role id.
     */

    public string $id;

    /*
     * The role name.
     */

    public string $name;

    /*
     * The role color.
     */

    public int $color;

    /*
     * Whether the role is hoisted.
     */

    public bool $hoist;

    /*
     * The role icon hash.
     */

    public ?string $icon;

    /*
     * Whether the role is managed.
     */

    public bool $managed;

    /*
     * Whether the role is mentionable.
     */

    public bool $mentionable;

    /*
     * The role permissions.
     */

    public string $permissions;

    /*
     * The role position.
     */

    public int $position;

    /*
     * The role tags.
     */

    public ?object $tags;

    /*
    * The roles Flags
    */

    public int $flags;

    public function __construct(object $data)
    {
        $this->id = $data->id;
        $this->name = $data->name;
        $this->color = $data->color;
        $this->hoist = $data->hoist;
        $this->icon = $data->icon;
        $this->managed = $data->managed;
        $this->mentionable = $data->mentionable;
        $this->permissions = $data->permissions;
        $this->position = $data->position;
        $this->flags = $data->flags;
    }
}
