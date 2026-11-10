<?php

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;
use function get_post_type;
use function get_post_type_object;
use function sanitize_html_class;
use function sprintf;

class PostTypeBadgeBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/post-type-badge';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if ($this->isTemplateEditor()) {
            $label = __('Post', 'timeless');
        } else {
            $postId = $this->resolvePostId($block);
            if (!$postId) {
                return '';
            }

            $ptype = get_post_type($postId);
            $ptypeObj = get_post_type_object($ptype);
            $label = $ptypeObj ? ($ptypeObj->labels->singular_name ?? $ptypeObj->label) : $ptype;

            $customLabels = apply_filters('timeless/post_type_badge/labels', []);
            if (is_array($customLabels) && isset($customLabels[$ptype]) && is_string($customLabels[$ptype]) && $customLabels[$ptype] !== '') {
                $label = $customLabels[$ptype];
            }
        }

        $attrs = is_array($attributes) ? $attributes : (array) $attributes;

        $displayType = $attrs['displayType'] ?? 'absolute';
        $position = $attrs['position'] ?? 'top-right';
        $offsetX = $attrs['offsetX'] ?? '12px';
        $offsetY = $attrs['offsetY'] ?? '12px';
        $bg = $attrs['backgroundColor'] ?? '#2e7d32';
        $color = $attrs['textColor'] ?? '#fff';
        $radius = (int) ($attrs['borderRadius'] ?? 8);
        $showLabel = $attrs['showLabel'] ?? true;

        if (!$showLabel) {
            return '';
        }

        $classes = ['wp-block-jankx-post-type-badge'];
        if ($displayType === 'absolute') {
            $classes[] = 'position-' . sanitize_html_class($position);
        } else {
            $classes[] = 'display-normal';
        }

        $styleParts = [];
        if ($displayType === 'absolute') {
            if (strpos($position, 'top') !== false) {
                $styleParts[] = 'top:' . esc_attr($offsetY) . ';';
            } else {
                $styleParts[] = 'bottom:' . esc_attr($offsetY) . ';';
            }
            if (strpos($position, 'right') !== false) {
                $styleParts[] = 'right:' . esc_attr($offsetX) . ';';
            } else {
                $styleParts[] = 'left:' . esc_attr($offsetX) . ';';
            }
        }

        $styleParts[] = 'background:' . esc_attr($bg) . ';';
        $styleParts[] = 'color:' . esc_attr($color) . ';';
        $styleParts[] = 'border-radius:' . $radius . 'px;';

        $wrapperAttributes = [
            'class' => 'jankx-post-type-badge ' . implode(' ', $classes),
        ];

        return sprintf(
            '<div %s style="%s">%s</div>',
            $this->attributesToHtml($wrapperAttributes),
            esc_attr(implode(' ', $styleParts)),
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

    private function isTemplateEditor(): bool
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($requestUri, '/wp-json/wp/v2/template') !== false || strpos($requestUri, '/wp-json/wp/v2/template-part') !== false) {
                return true;
            }
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && ($screen->id === 'site-editor' || $screen->id === 'appearance_page_gutenberg-edit-site')) {
                return true;
            }
        }

        if (isset($_GET['_wp-find-template']) || (isset($_GET['postType']) && $_GET['postType'] === 'wp_template')) {
            return true;
        }

        global $post;
        if ((is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) && (empty($post) || empty($post->post_content))) {
            return true;
        }

        return false;
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
