<?php

/**
 * Timeless — PrestoWorld theme loader.
 *
 * Loaded by functions.php when PRESTOWORLD is defined. Only PrestoWorld public
 * APIs are used here; the WordPress compile layer (Core Legacy shims) is what
 * translates any WordPress-shaped calls the theme might still make.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/blocks.php';
require_once __DIR__ . '/context-blocks.php';
