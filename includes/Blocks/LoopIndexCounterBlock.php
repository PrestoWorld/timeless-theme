<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function number_format_i18n;

class LoopIndexCounterBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/loop-index-counter';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $start = (int) ($attributes['start'] ?? 1);
        $padStart = (int) ($attributes['padStart'] ?? 0);
        $prefix = $attributes['prefix'] ?? '';
        $suffix = $attributes['suffix'] ?? '';
        $className = $attributes['className'] ?? '';

        // Resolve loop index from block context or block sibling index
        $index = $start;

        if ($block instanceof \WP_Block && !empty($block->context['loopIndex'])) {
            $index = (int) $block->context['loopIndex'];
        } elseif ($block instanceof \WP_Block && isset($block->context['wp:query'])) {
            $query = $block->context['wp:query'];
            if ($query instanceof \WP_Query) {
                $index = $query->current_post + $start;
            }
        }

        if ($padStart > 0) {
            $indexStr = str_pad((string) $index, $padStart, '0', STR_PAD_LEFT);
        } else {
            $indexStr = (string) $index;
        }

        $classes = array_filter([
            'wp-block-jankx-loop-index-counter',
            $className,
        ]);

        return sprintf(
            '<div class="%s">%s%s%s</div>',
            esc_attr(implode(' ', $classes)),
            esc_html($prefix),
            esc_html(number_format_i18n($indexStr)),
            esc_html($suffix)
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
