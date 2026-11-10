<?php

namespace Timeless\Blocks;

use function esc_html;
use function get_block_wrapper_attributes;
use function number_format_i18n;

class SearchResultsCountBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/search-results-count';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $count = $this->resolveCount($block);

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'jankx-search-results-count',
        ]);

        return sprintf(
            '<span %1$s>%2$s</span>',
            $wrapperAttributes,
            esc_html(number_format_i18n($count))
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function resolveCount(?\WP_Block $block): int
    {
        if ($block instanceof \WP_Block && !empty($block->context['query'])) {
            $queryArgs = $block->context['query'];
            $query = new \WP_Query($queryArgs);
            return (int) $query->found_posts;
        }

        global $wp_query;
        if ($wp_query instanceof \WP_Query) {
            return (int) $wp_query->found_posts;
        }

        return 0;
    }
}
