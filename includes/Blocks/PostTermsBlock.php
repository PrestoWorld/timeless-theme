<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function get_the_terms;
use function get_term_link;
use function implode;
use function is_wp_error;

class PostTermsBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/post-terms';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $postId = $this->resolvePostId($block);
        if (!$postId) {
            return '';
        }

        $taxonomy = $attributes['taxonomy'] ?? 'category';
        $displayStyle = $attributes['displayStyle'] ?? 'default';
        $separator = $attributes['separator'] ?? ', ';
        $layout = $attributes['layout'] ?? 'inline';
        $linkToTerm = $attributes['linkToTerm'] ?? true;
        $termTextColor = $attributes['termTextColor'] ?? '';
        $termBgColor = $attributes['termBackgroundColor'] ?? '';

        $termStyle = '';
        if ($termTextColor !== '') {
            $termStyle .= 'color:' . esc_attr($termTextColor) . ';';
        }
        if ($termBgColor !== '') {
            $termStyle .= 'background-color:' . esc_attr($termBgColor) . ';padding:2px 8px;border-radius:4px;';
        }

        $terms = get_the_terms($postId, $taxonomy);

        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        $termItems = [];
        foreach ($terms as $term) {
            $label = $term->name;
            if ($displayStyle === 'hashtag') {
                $label = '#' . $label;
            }

            $itemContent = esc_html($label);
            if ($linkToTerm) {
                $itemContent = sprintf(
                    '<a href="%s" class="jankx-post-term-link" style="color:inherit;">%s</a>',
                    esc_url(get_term_link($term)),
                    $itemContent
                );
            }

            $termItems[] = sprintf(
                '<span class="jankx-post-term-inner" style="%s">%s</span>',
                esc_attr($termStyle),
                $itemContent
            );
        }

        $wrapperAttributes = [
            'class' => sprintf(
                'display-style-%s layout-%s',
                sanitize_html_class($displayStyle),
                sanitize_html_class($layout)
            ),
        ];

        $output = '';
        if ($layout === 'inline') {
            $separatorHtml = sprintf('<span class="jankx-post-term-separator">%s</span>', esc_html($separator));
            $items = array_map(function ($item) {
                return sprintf('<span class="jankx-post-term">%s</span>', $item);
            }, $termItems);
            $output = implode($separatorHtml, $items);
        } else {
            $output = implode('', array_map(function ($item) {
                return sprintf('<div class="jankx-post-term">%s</div>', $item);
            }, $termItems));
        }

        return sprintf(
            '<div %s>%s</div>',
            $this->attributesToHtml($wrapperAttributes),
            $output
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

    private function attributesToHtml(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $key => $value) {
            if ($value !== '' && $value !== null) {
                $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
            }
        }
        return $html;
    }
}
