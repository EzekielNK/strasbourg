<?php

$static_dir = dirname(__FILE__);
$root = '/homepages/18/d771982773/htdocs/www/sous-domaine/strasbourg/';
$plugin_root = $root . 'wp-content/plugins/psn-pagespeed-ninja';

define('RESSIO_STATICDIR', $static_dir);

require $plugin_root . '/ress/fetch.php';
