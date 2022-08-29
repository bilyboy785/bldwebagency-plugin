=== BLD Web Agency ===
Contributors: martinbouillaud
Donate link: https://paypal.me/martinbouillaud
Tags: customize, login, login screen, logo, custom logo
Requires at least: 5.9
Tested up to: 6.0.1

Customize Login Logo and add some tweaks

== Description ==

Ce plugin modifie le Login Logo à la connexion admin Wordpress. Il récupère ce logo sur l'URL `https://www.bldwebagency.fr/wp-content/uploads/2022/08/`
312px de large pour une meilleure lisibilité.

This plugin also works in the `mu-plugins` directory.

== Installation ==

1. Install the plugin and activate it.

2. Create a PNG image with a transparent background, tightly cropped, with a recommended width of 312 pixels.

3. Upload the PNG image to your WordPress content directory (`/wp-content/`, by default), and name the file `login-logo.png`.

4. If you have a multisite install with more than one network, you can also use `login-logo-network-{NETWORK ID}.png` to assign a different login logo to each network.

5. If you have a multisite install, you can also use `login-logo-site-{$blog_id}.png` to assign a different login logo to each site.

6. Done! The login screen will now use your logo.

== Screenshots ==

1. A login screen with a custom logo

2. A source image

== Changelog ==

= 1.2 =
* Add some tweaks for performances

= 1.1 =
* Add function to download png file from BLD Web Agency file

= 1.0 =
* Original first version

== Upgrade Notice ==

