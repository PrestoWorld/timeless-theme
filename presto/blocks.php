<?php

/**
 * Timeless — renderer block registrations (PrestoWorld runtime).
 *
 * The theme owns its Gutenberg block renderers: it maps block names to the
 * renderer classes that live in this theme. The framework core only ships
 * `core/*` renderers and never hardcodes a theme's block names.
 */

declare(strict_types=1);

use PrestoWorld\Modules\Gutenberg\Renderer\Blocks\BlockFactory;

$timelessBlocks = [
    'PrestoWorld\\Theme\\Timeless\\Blocks\\DynamicDataLayoutBlock'
        => __DIR__ . '/blocks/DynamicDataLayoutBlock.php',
    'PrestoWorld\\Theme\\Timeless\\Blocks\\DynamicDataTemplateBlock'
        => __DIR__ . '/blocks/DynamicDataTemplateBlock.php',
    'PrestoWorld\\Theme\\Timeless\\Blocks\\HumanReadablePostDateBlock'
        => __DIR__ . '/blocks/HumanReadablePostDateBlock.php',
];

foreach ($timelessBlocks as $class => $file) {
    if (!class_exists($class) && is_file($file)) {
        require_once $file;
    }
}

/**
 * Map: renderer class => [canonical name, ...legacy aliases].
 *
 * Legacy `timeless/*` aliases keep content authored before the theme switch
 * rendering while it is migrated to the `timeless/*` namespace.
 */
$renderers = [
    'PrestoWorld\\Theme\\Timeless\\Blocks\\DynamicDataLayoutBlock' => [
        'timeless/dynamic-data-layout',
        'timeless/dynamic-data-layout',
    ],
    'PrestoWorld\\Theme\\Timeless\\Blocks\\DynamicDataTemplateBlock' => [
        'timeless/dynamic-data-template',
        'timeless/dynamic-data-template',
    ],
    'PrestoWorld\\Theme\\Timeless\\Blocks\\HumanReadablePostDateBlock' => [
        'timeless/human-readable-post-date',
        'timeless/human-readable-post-date',
    ],
];

foreach ($renderers as $class => $names) {
    if (!class_exists($class)) {
        continue;
    }

    foreach ($names as $name) {
        BlockFactory::registerBlock($name, $class);
    }
}
