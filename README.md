[comment]: <> (# Larascord)

[comment]: <> (> :warning: This project is under heavy development and is not ready for use.)

[comment]: <> (Larascord is a package that allows you to interact with the Discord API within your Laravel application.)
<p align="center">
    <a href="https://github.com/JakyeRU/Larascord" target="_blank">
        <img src="https://raw.githubusercontent.com/JakyeRU/Larascord/main/Larascord-transparent.png" height=200>
    </a>
</p>

# About Larascord
Larascord is a package that allows you to authenticate users in your Laravel application using Discord.

# Installation
## Discord
### Step 1: Create a new Discord application (skip if you already have one)
* Go to [Discord Developer Portal](https://discord.com/developers/applications) and press the "New Application" button.
* Choose a name for your application and press the "Create" button.


### Step 2: Create a new OAuth2 redirect for your application
* Go to the "OAuth2" tab of your application and press the "Add Redirect" button.
  * The redirect URL should be your application's callback URL.
    * Example:
      * `http://localhost:8000/larascord/callback`
      * `https://myapplication.com/larascord/callback`
* Press the "Save Changes" button.



## Laravel
> :warning: You must use this package in a fresh Laravel application otherwise unexpected behavior may occur.
### Step 1: Install Larascord
* `composer require jakyeru/larascord`
### Step 2: Run the installation command
* `php artisan larascord:install`
### Step 3: Follow Larascord's instructions
* You can get your Discord application's `CLIENT ID` and `CLIENT SECRET` from the "OAuth2" tab of your application.
  * ![](https://i.imgur.com/YJnM4H5.png)
* You can get your Discord application's bot `TOKEN` from the "Bot" tab of your application. (_this is used only to validate the data you provided with Discord_)
  * ![](https://i.imgur.com/ppLVjRY.png)
* The `REDIRECT URI` has to be the **same** as the one you provided in your application's OAuth2 redirect.

Your application should now be able to authenticate users using Discord.

---
# Larascord Routes
> :hint: These routes can be found in the `routes/auth.php` file.
> 
| Route Name | URL | Description | Action | Method |
| ---------- | ---- | ----------- | ------ | ------ |
| `login` | `/login` | Redirects the user to Discord's OAuth2 authorization page. | REDIRECT | `GET` |
| `larascord.login` | `/larascord/callback` | Callback route for Discord OAuth2 authentication. | `DiscordController@login` | `GET` |
| `larascord.logout` | `/larascord/logout` | Invalidates the current session.| `DiscordController@logout` | `POST` |
