<p align="center">
    <a href="https://github.com/JakyeRU/Larascord" target="_blank">
        <img src="https://raw.githubusercontent.com/JakyeRU/Larascord/main/Larascord-transparent.png" height=200>
    </a>
</p>

<p align="center">
    <img src="https://img.shields.io/github/workflow/status/JakyeRU/Larascord/Run%20tests?style=for-the-badge" alt="build">
    <img src="https://img.shields.io/github/v/release/jakyeru/larascord?color=blue&style=for-the-badge" alt="release">
</p>

![](./alerts/php-version.svg)

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
```shell
composer require jakyeru/larascord
```
### Step 2: Run the installation command
```shell
php artisan larascord:install
```
### Step 3: Follow Larascord's instructions
* You can get your Discord application's `CLIENT ID` and `CLIENT SECRET` from the "OAuth2" tab of your application.
![](https://i.imgur.com/YJnM4H5.png)

Your application should now be able to authenticate users using Discord.

## Configuration
#### You can publish Larascord's configuration using the following commands:
```shell
php artisan larascord:publish
```
```shell
php artisan vendor:publish --provider="Jakyeru\Larascord\LarascordServiceProvider" --tag="config"
```
#### You can also forcefully publish Larascord's configuration, overwriting any existing configuration:
```shell
php artisan larascord:publish --force
```
_You shouldn't need these commands as the configuration is automatically published when the package is installed._

---
# Larascord Routes
| Route Name | URI | Description | Action | Method |
| ---------- | ---- | ----------- | ------ | ------ |
| `login` | `/login` | Redirects the user to Discord's OAuth2 authorization page. | REDIRECT | `GET` |
| `logout` | `/logout` | Invalidates the current session. | `AuthenticatedSessionController@destroy` | `POST` |
| `larascord.login` | `/larascord/callback` | Callback route for Discord OAuth2 authentication. | `DiscordController@handle` | `GET` |
| `larascord.refresh_token` | `/larascord/refresh-token` | Redirects to the login page. (Used to access secure parts of the application through the middleware `password.confirm`.) | REDIRECT | `GET` |

---
# Possible Errors
## `Invalid OAuth2 redirect_uri`
This error occurs when your `redirect_uri` is not listed in your application's OAuth2 redirects.

If you are sure your `redirect_uri` is correct, make sure that `APP_URL` is correct in `.env`.

## `cURL error 60: SSL certificate problem: unable to get local issuer certificate`
This issue is not caused by Larascord. You can find a fix in [#27](https://github.com/JakyeRU/Larascord/issues/27).

---
# Version Compatibility
| Laravel Version | Larascord Version   |
|-----------------|---------------------|
| 8               | 1.X.X, 2.X.X, 3.X.X |
| 9               | 4.X.X               |

---

If you encounter any other error(s), please open an issue on [GitHub](https://github.com/JakyeRU/Larascord/issues/new/choose).
