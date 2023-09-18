<?php
namespace Jakyeru\Larascord\Types;

class GuildPreview
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
     * The guild splash hash.
     */
    public ?string $splash;

    /*
     * The guild discovery splash hash.
     */
    public ?string $discovery_splash;

    /*
     * The guild emojis.
     */
    public array $emojis;

    /*
     * The guild features.
     */
    public array $features;

    /*
     * The approximate count of members in the guild.
     */
    public int $approximate_member_count;

    /*
     * The approximate count of active members in the guild.
     */
    public int $approximate_presence_count;

    /*
     * The guild description.
     */
    public ?string $description;

    /*
     * The guild stickers.
     */
    public array $stickers;

    public function __construct(object $data)
    {
        $this->id = $data->id;
        $this->name = $data->name;
        $this->icon = $data->icon;
        $this->splash = $data->splash;
        $this->discovery_splash = $data->discovery_splash;
        $this->emojis = $data->emojis;
        $this->features = $data->features;
        $this->approximate_member_count = $data->approximate_member_count;
        $this->approximate_presence_count = $data->approximate_presence_count;
        $this->description = $data->description;
        $this->stickers = $data->stickers;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
