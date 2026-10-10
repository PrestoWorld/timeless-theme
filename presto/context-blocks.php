<?php

/**
 * Timeless — context block registrations (PrestoWorld runtime).
 *
 * Registrations are deferred through ThemeBooter so the theme boots no matter
 * whether the ContextBuilder module (owner of the BlockRegistry) booted before
 * or after the Gutenberg module that boots this theme.
 */

declare(strict_types=1);

use PrestoWorld\Modules\ContextBuilder\BlockRegistry;
use PrestoWorld\Modules\Gutenberg\Theme\ThemeBooter;
use PrestoWorld\Theme\Timeless\ContextBlocks\DynamicDataLayoutBlock;
use PrestoWorld\Theme\Timeless\ContextBlocks\DynamicDataTemplateBlock;
use PrestoWorld\Theme\Timeless\ContextBlocks\HumanReadablePostDateBlock;

foreach ([
    DynamicDataLayoutBlock::class => __DIR__ . '/context-blocks/DynamicDataLayoutBlock.php',
    DynamicDataTemplateBlock::class => __DIR__ . '/context-blocks/DynamicDataTemplateBlock.php',
    HumanReadablePostDateBlock::class => __DIR__ . '/context-blocks/HumanReadablePostDateBlock.php',
] as $class => $file) {
    if (!class_exists($class) && is_file($file)) {
        require_once $file;
    }
}

$registerTimelessContextBlocks = static function (BlockRegistry $registry): void {
    $registry->register(new DynamicDataLayoutBlock());
    $registry->register(new DynamicDataTemplateBlock());
    $registry->register(new HumanReadablePostDateBlock());

    // Legacy `timeless/*` aliases for content authored before the theme switch.
    $registry->register(new DynamicDataLayoutBlock('timeless/dynamic-data-layout'));
    $registry->register(new DynamicDataTemplateBlock('timeless/dynamic-data-template'));
    $registry->register(new HumanReadablePostDateBlock('timeless/human-readable-post-date'));
};

$registry = ThemeBooter::blockRegistry();
if ($registry instanceof BlockRegistry) {
    $registerTimelessContextBlocks($registry);
} else {
    ThemeBooter::defer(static function () use ($registerTimelessContextBlocks): void {
        $registry = ThemeBooter::blockRegistry();
        if ($registry instanceof BlockRegistry) {
            $registerTimelessContextBlocks($registry);
        }
    });
}
