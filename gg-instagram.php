<?php
/**
 * Plugin Name: GG Instagram
 * Description: A plugin to connect and manage Instagram profiles for each site in a WordPress multisite network.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

defined('ABSPATH') || exit;

// Include Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the plugin
function gg_instagram_init() {
    $plugin = new \GGInstagram\Plugin();
    $plugin->run();
}
add_action('plugins_loaded', 'gg_instagram_init');
