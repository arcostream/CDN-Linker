====================================
Speed Cache - Wordpress Plugin
====================================
:Info: See `github <http://github.com/arcostream/CDN-Linker>`_ for the latest source.
:Tags: CDN,links,media,performance,distribution,accelerator,content,speed,cloud
:Requires at least: 2.7
:Tested up to: 3.1.2

The one-click speed boost to your Wordpress site.
Decreases load on your server by automatically setting up and configuring a CDN for you.

Description
============
Replaces the blog URL by another for all files under `wp-content` and `wp-includes`.
That way static content will be delivered by our CDN for you.

Installation
=============

1. Upload the plugin to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Under `Settings`, click on `Sign Up` or enter your existing customer credentials.

That's it! Everything else is done automatically.

Frequently Asked Questions
===========================

How to uninstall?
  Either deactivate the plugin or delete the plugin's directory.

Why another such plugin?
  As many WP plugins don't correctly include JS and CSS files most of the current CDN plugins cannot
  rewrite links correctly. They rely on the high-level WP API.

  This plugin does its rewriting on the lowest level possible - PHP itself.

How does it work?
  After your blog pages have been rendered but before sending them to the visitor,
  it will rewrite links pointing to `wp-content` and `wp-includes`. That rewriting will simply
  replace your blog URL with a CDN's address that is generated for you.

  Thus files are pulled from the CDN.

Do I need a CDN?
  No. By signing up with us through the plugin our CDN will be configured for you automatically.
  That is, we set up a domain name for you and make all the settings you need. Trust the experts.

Is it compatible to plugin XY?
  Yes, by design it is compatible to all plugins. It hooks into a PHP function ob_start_
  and there does the string replacement. Therefore, no Wordpress function is altered, overwritten or modified in any way.

What other plugins do you recommend?
  Now that you can offload all the files such as images, music or CSS, you should serve your blog posts as static files to
  decrease load on your server. I recommend SuperCache-Plus_ as it will maintain, update and create that static files from
  dynamic content for you.

I need support!
  Don't hesitate to contact us: http://arcostream.com/company.php

.. _ob_start:        http://us2.php.net/manual/en/function.ob-start.php
.. _SuperCache-Plus: http://murmatrons.armadillo.homeip.net/features/experimental-eaccelerator-wp-super-cache
