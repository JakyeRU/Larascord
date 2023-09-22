<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscordUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['id', 'username', 'discriminator', 'global_name', 'email', 'avatar', 'verified', 'banner', 'accent_color', 'public_flags', 'flags', 'locale', 'premium_type', 'mfa_enabled'];
}
