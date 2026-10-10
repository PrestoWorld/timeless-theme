<?php
/**
 * Timeless theme bootstrap.
 *
 * The theme decides how to load based on the running runtime:
 *   - Under WordPress it behaves like a normal classic theme.
 *   - Under PrestoWorld (constant PRESTOWORLD) it hands off to the theme's
 *     own loader at presto/bootstrap.php, which registers the theme's block
 *     renderers using PrestoWorld APIs only (no raw WordPress functions).
 *
 * @package Timeless
 */

if (!defined('ABSPATH')) {
    exit;
}

if (defined('PRESTOWORLD') && PRESTOWORLD) {
    require_once get_template_directory() . '/presto/bootstrap.php';

    return;
}

if (!function_exists('timeless_setup')) {
    /**
     * WordPress teardown-up: matches the hook PrestoWorld fires during
     * theme setup so the same callback runs in both runtimes.
     */
    function timeless_setup(): void
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', ['style', 'script']);
    }
}

add_action('after_setup_theme', 'timeless_setup');
