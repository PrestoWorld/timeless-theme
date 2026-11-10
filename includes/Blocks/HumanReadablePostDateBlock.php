<?php

namespace Timeless\Blocks;

use function current_time;
use function esc_attr;
use function esc_html;
use function get_post;
use function get_the_time;
use function human_time_diff;
use function sprintf;

class HumanReadablePostDateBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/human-readable-post-date';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $postId = $this->resolvePostId($block);
        if (!$postId) {
            return '';
        }

        $diff = human_time_diff(get_the_time('U', $postId), current_time('timestamp'));
        $humanDate = sprintf(__('%s ago', 'timeless'), $diff);
        $showIcon = $attributes['showIcon'] ?? true;

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'jankx-human-readable-post-date',
        ]);

        return sprintf(
            '<div %1$s>%2$s%3$s</div>',
            $wrapperAttributes,
            $showIcon ? '<span class="post-date-icon" style="margin-right:6px;">&#x1f552;</span>' : '',
            esc_html($humanDate)
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function resolvePostId(?\WP_Block $block): int
    {
        if ($block instanceof \WP_Block && !empty($block->context['postId'])) {
            return (int) $block->context['postId'];
        }

        $postId = get_the_ID();
        if ($postId) {
            return (int) $postId;
        }

        global $post;
        if ($post && isset($post->ID)) {
            return (int) $post->ID;
        }

        return 0;
    }
}
