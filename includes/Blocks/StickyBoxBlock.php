<?php

namespace Timeless\Blocks;

use function absint;
use function esc_attr;
use function get_block_wrapper_attributes;

class StickyBoxBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/sticky-box';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $stickyEnabled = !empty($attributes['stickyEnabled']);
        $offsetTop = absint($attributes['offsetTop'] ?? 16);

        $classes = ['jankx-sticky-box'];
        if ($stickyEnabled) {
            $classes[] = 'sticky-enabled';
        }

        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', $classes),
            'style' => '--sticky-top: ' . $offsetTop . 'px;',
        ]);

        return sprintf('<div %s>%s</div>', $wrapper_attributes, $content);
    }
}
