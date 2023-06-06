<?php

namespace Jakyeru\Larascord\Types;

class Connection
{
    /**
     * The type of the connection.
     */
    public string $type;

    /**
     * The id of the connection account.
     */
    public string $id;

    /**
     * The name of the connection account.
     */
    public string $name;

    /**
     * The visibility of the connection.
     */
    public bool $visibility;

    /**
     * The friend sync of the connection.
     */
    public bool $friend_sync;

    /**
     * Whether the connection will show in an activity.
     */
    public bool $show_activity;

    /**
     * Whether the connection is verified.
     */
    public bool $verified;

    /**
     * Whether the connection is two-way linked.
     */
    public bool $two_way_link;

    /**
     * Metadata visibility of the connection.
     */
    public int $metadata_visibility;

    /**
     * Connection constructor.
     */
    public function __construct(object $data)
    {
        $this->type = $data->type;
        $this->id = $data->id;
        $this->name = $data->name;
        $this->visibility = $data->visibility;
        $this->friend_sync = $data->friend_sync;
        $this->show_activity = $data->show_activity;
        $this->verified = $data->verified;
        $this->two_way_link = $data->two_way_link;
        $this->metadata_visibility = $data->metadata_visibility;
    }

    /**
     * Converts the Connection to an array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'visibility' => $this->visibility,
            'friend_sync' => $this->friend_sync,
            'show_activity' => $this->show_activity,
            'verified' => $this->verified,
            'two_way_link' => $this->two_way_link,
            'metadata_visibility' => $this->metadata_visibility
        ];
    }
}