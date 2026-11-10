<?php

namespace Timeless\Blocks;

use function esc_html;
use function get_comments_number;
use function get_the_ID;
use function number_format_i18n;
use function sprintf;

class CommentCountBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/comment-count';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $postId = $this->resolvePostId($block);
        if (!$postId) {
            return '';
        }

        $count = max(0, (int) get_comments_number($postId));
        $label = _n('Comment', 'Comments', $count, 'timeless');

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'jankx-comment-count',
        ]);

        return sprintf(
            '<div %1$s><span class="comment-count-number">%2$s</span><span class="comment-count-text">%3$s</span></div>',
            $wrapperAttributes,
            esc_html(number_format_i18n($count)),
            esc_html($label)
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
