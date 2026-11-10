<?php

namespace Timeless\Blocks;

use function add_filter;
use function apply_filters;
use function array_pop;
use function count;
use function end;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_block_wrapper_attributes;
use function get_current_screen;
use function get_post;
use function in_array;
use function is_admin;
use function is_wp_error;
use function number_format_i18n;
use function parse_blocks;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strpos;
use function strtolower;
use function strtr;
use function strip_tags;
use function trim;
use function wp_strip_all_tags;

class TableOfContentBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/table-of-content';

    protected array $headings = [];

    protected array $headingAnchors = [];

    protected array $anchorCounts = [];

    protected bool $usesHTMLHeadings = false;

    protected static bool $contentFilterAdded = false;

    public function boot(): void
    {
        add_filter('render_block_core/heading', [$this, 'filterHeadingBlock'], 10, 2);
    }

    public function filterHeadingBlock(string $block_content, array $block): string
    {
        if (empty($block_content)) {
            return $block_content;
        }

        if (preg_match('/id=["\']([^"\']+)["\']/', $block_content)) {
            return $block_content;
        }

        preg_match('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $block_content, $matches);
        if (empty($matches[2])) {
            return $block_content;
        }

        $level = (int) $matches[1];
        $heading_text = strip_tags($matches[2]);
        $heading_text = trim($heading_text);

        $heading_key = $level . ':' . $heading_text;

        $anchor = '';
        if (isset($this->headingAnchors[$heading_key])) {
            $anchor = $this->headingAnchors[$heading_key];
        } elseif (!empty($block['attrs']['anchor'])) {
            $anchor = $block['attrs']['anchor'];
        } else {
            $anchor = $this->generateAnchor($heading_text);
            $this->headingAnchors[$heading_key] = $anchor;
        }

        $block_content = preg_replace(
            '/(<h[1-6])([^>]*)(>)/i',
            '$1$2 id="' . esc_attr($anchor) . '"$3',
            $block_content,
            1
        );

        return $block_content;
    }

    public function filterContentHeadings(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        if (!preg_match('/<h[1-6]/i', $content) || preg_match('/<!--\s*wp:/', $content)) {
            return $content;
        }

        if (empty($this->headingAnchors)) {
            return $content;
        }

        preg_match_all('/<h([1-6])([^>]*?)>(.*?)<\/h[1-6]>/is', $content, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            foreach ($matches as $match) {
                $level = (int) $match[1];
                $attrs = $match[2];
                $heading_text = strip_tags($match[3]);
                $heading_text = trim($heading_text);

                if (empty($heading_text)) {
                    continue;
                }

                if (preg_match('/id=["\']([^"\']+)["\']/', $attrs)) {
                    continue;
                }

                $heading_key = $level . ':' . $heading_text;
                $anchor = '';

                if (isset($this->headingAnchors[$heading_key])) {
                    $anchor = $this->headingAnchors[$heading_key];
                } else {
                    $anchor = $this->generateAnchor($heading_text);
                    $this->headingAnchors[$heading_key] = $anchor;
                }

                $new_heading = '<h' . $level . $attrs . ' id="' . esc_attr($anchor) . '">' . $match[3] . '</h' . $level . '>';
                $content = str_replace($match[0], $new_heading, $content);
            }
        }

        return $content;
    }

    protected function generateAnchor(string $text): string
    {
        $anchor = strtolower($text);
        $anchor = $this->removeVietnameseDiacritics($anchor);
        $anchor = preg_replace('/[^a-z0-9]+/', '-', $anchor);
        $anchor = trim($anchor, '-');

        if (isset($this->anchorCounts[$anchor])) {
            $this->anchorCounts[$anchor]++;
            $anchor = $anchor . '-' . $this->anchorCounts[$anchor];
        } else {
            $this->anchorCounts[$anchor] = 0;
        }

        return $anchor;
    }

    protected function removeVietnameseDiacritics(string $str): string
    {
        $diacritics = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        ];

        return strtr($str, $diacritics);
    }

    protected function parseBlocksForHeadings(array $blocks, array &$headings, int $min_level = 1, int $max_level = 6): void
    {
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'jankx/table-of-content') {
                continue;
            }

            if ($block['blockName'] === 'core/heading') {
                $level = $block['attrs']['level'] ?? 2;

                if ($level < $min_level || $level > $max_level) {
                    continue;
                }

                $text = wp_strip_all_tags($block['innerHTML']);
                $text = trim($text);

                if (!empty($text)) {
                    $anchor = '';
                    if (!empty($block['attrs']['anchor'])) {
                        $anchor = $block['attrs']['anchor'];
                    } else {
                        $anchor = $this->generateAnchor($text);
                    }

                    $heading_key = $level . ':' . $text;
                    $this->headingAnchors[$heading_key] = $anchor;

                    $headings[] = [
                        'id' => $anchor,
                        'text' => $text,
                        'level' => $level,
                        'children' => [],
                        'isExpanded' => false,
                    ];
                }
            }

            if (!empty($block['innerBlocks'])) {
                $this->parseBlocksForHeadings($block['innerBlocks'], $headings, $min_level, $max_level);
            }
        }
    }

    protected function parseHTMLForHeadings(string $content, array &$headings, int $min_level = 1, int $max_level = 6): void
    {
        preg_match_all('/<h([1-6])([^>]*?)>(.*?)<\/h[1-6]>/is', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return;
        }

        foreach ($matches as $match) {
            $level = (int) $match[1];
            $attrs = $match[2];
            $heading_html = $match[3];

            if ($level < $min_level || $level > $max_level) {
                continue;
            }

            $text = wp_strip_all_tags($heading_html);
            $text = trim($text);

            if (empty($text)) {
                continue;
            }

            $anchor = '';

            if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $id_match)) {
                $anchor = $id_match[1];
            } else {
                $heading_key = $level . ':' . $text;
                if (isset($this->headingAnchors[$heading_key])) {
                    $anchor = $this->headingAnchors[$heading_key];
                } else {
                    $anchor = $this->generateAnchor($text);
                    $this->headingAnchors[$heading_key] = $anchor;
                }
            }

            $headings[] = [
                'id' => $anchor,
                'text' => $text,
                'level' => $level,
                'children' => [],
                'isExpanded' => false,
            ];
        }
    }

    protected function extractHeadings(string $content, int $min_level = 1, int $max_level = 6): array
    {
        if (empty($content)) {
            return [];
        }

        $this->headingAnchors = [];
        $this->anchorCounts = [];
        $this->usesHTMLHeadings = false;

        $headings = [];

        $blocks = parse_blocks($content);

        $has_blocks = false;
        foreach ($blocks as $block) {
            if (!empty($block['blockName'])) {
                $has_blocks = true;
                break;
            }
        }

        if ($has_blocks) {
            $this->usesHTMLHeadings = false;
            $this->parseBlocksForHeadings($blocks, $headings, $min_level, $max_level);
        } else {
            $this->usesHTMLHeadings = true;

            $html_content = '';
            if (function_exists('do_blocks')) {
                $html_content = do_blocks($content);
            } else {
                $html_content = $content;
            }

            $this->parseHTMLForHeadings($html_content, $headings, $min_level, $max_level);
        }

        return $this->buildHierarchy($headings);
    }

    protected function buildHierarchy(array $headings): array
    {
        $hierarchy = [];
        $stack = [];

        foreach ($headings as $heading) {
            $level = $heading['level'];

            while (!empty($stack) && end($stack)['level'] >= $level) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $hierarchy[] = $heading;
                $stack[] = &$hierarchy[count($hierarchy) - 1];
            } else {
                $parent = &$stack[count($stack) - 1];
                $parent['children'][] = $heading;
                $stack[] = &$parent['children'][count($parent['children']) - 1];
            }
        }

        return $hierarchy;
    }

    protected function isTemplateEditor(): bool
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($request_uri, '/wp-json/wp/v2/template') !== false ||
                strpos($request_uri, '/wp-json/wp/v2/template-part') !== false) {
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

        $post = get_post();
        if (empty($post) || empty($post->post_content)) {
            if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
                return true;
            }
        }

        return false;
    }

    protected function getMockHeadings(int $min_level = 1, int $max_level = 6): array
    {
        $mock_data = [
            [
                'id' => 'ban-can-biet',
                'text' => 'Bạn cần biết!',
                'level' => max(1, min(2, $min_level)),
                'children' => [],
                'isExpanded' => false,
            ],
            [
                'id' => 'mo-hinh-van-hanh',
                'text' => 'Mô hình vận hành',
                'level' => max(1, min(2, $min_level)),
                'children' => [
                    [
                        'id' => 'hoat-dong-sach-cu-gia-re',
                        'text' => 'HOẠT ĐỘNG SÁCH CŨ GIÁ RẺ',
                        'level' => max(1, min(3, $max_level)),
                        'children' => [],
                        'isExpanded' => false,
                    ],
                ],
                'isExpanded' => true,
            ],
        ];

        $filtered_data = $this->filterHeadingsByLevel($mock_data, $min_level, $max_level);

        return $this->buildHierarchyFromMock($filtered_data);
    }

    protected function filterHeadingsByLevel(array $headings, int $min_level, int $max_level): array
    {
        $filtered = [];

        foreach ($headings as $heading) {
            if ($heading['level'] < $min_level || $heading['level'] > $max_level) {
                continue;
            }

            if (!empty($heading['children'])) {
                $heading['children'] = $this->filterHeadingsByLevel($heading['children'], $min_level, $max_level);
            }

            $filtered[] = $heading;
        }

        return $filtered;
    }

    protected function buildHierarchyFromMock(array $mock_data): array
    {
        $flat_headings = [];
        $this->flattenMockHeadings($mock_data, $flat_headings);

        return $this->buildHierarchy($flat_headings);
    }

    protected function flattenMockHeadings(array $headings, array &$flat): void
    {
        foreach ($headings as $heading) {
            $flat_item = [
                'id' => $heading['id'],
                'text' => $heading['text'],
                'level' => $heading['level'],
                'children' => [],
                'isExpanded' => $heading['isExpanded'] ?? false,
            ];
            $flat[] = $flat_item;

            if (!empty($heading['children'])) {
                $this->flattenMockHeadings($heading['children'], $flat);
            }
        }
    }

    protected function renderTOCList(array $items, string $listing_type, string $expand_icon_type, bool $default_expanded, bool $expand_first_item, int $depth = 0, bool $is_first_item = false, bool $is_hidden = false): string
    {
        if (empty($items)) {
            return '';
        }

        if ($listing_type === 'none') {
            $list_tag = 'div';
        } else {
            $list_tag = $listing_type === 'ol' ? 'ol' : 'ul';
        }

        $list_class = $depth === 0 ? 'toc-list toc-list--root' : 'toc-list toc-list--level-' . ($depth + 1);

        if ($listing_type === 'none') {
            $list_class .= ' toc-list--none';
        }

        if ($is_hidden) {
            $html = sprintf('<%s class="%s" style="display:none">', $list_tag, esc_attr($list_class));
        } else {
            $html = sprintf('<%s class="%s">', $list_tag, esc_attr($list_class));
        }

        foreach ($items as $index => $item) {
            $has_children = !empty($item['children']);

            if ($expand_icon_type === 'none') {
                $is_expanded = true;
            } else {
                $is_expanded = $default_expanded || ($is_first_item && $index === 0 && $expand_first_item);
            }

            $item_class = 'toc-item toc-item--level-' . $item['level'];

            if ($listing_type === 'none') {
                $html .= sprintf('<div class="%s">', esc_attr($item_class));
            } else {
                $html .= sprintf('<li class="%s">', esc_attr($item_class));
            }

            $html .= '<div class="toc-item__wrapper">';

            if ($has_children && $expand_icon_type !== 'none') {
                $toggle_class = $is_expanded ? 'toc-item__toggle is-expanded' : 'toc-item__toggle is-collapsed';
                $icon = $this->getExpandIcon($expand_icon_type, $is_expanded);
                $html .= sprintf(
                    '<button class="%s" type="button" aria-expanded="%s"><span class="toc-item__icon">%s</span></button>',
                    esc_attr($toggle_class),
                    $is_expanded ? 'true' : 'false',
                    esc_html($icon)
                );
            }

            $html .= sprintf(
                '<a href="#%s" class="toc-item__link">%s</a>',
                esc_attr($item['id']),
                esc_html($item['text'])
            );

            $html .= '</div>';

            if ($has_children) {
                $is_child_hidden = ($expand_icon_type !== 'none' && !$is_expanded);

                $child_html = $this->renderTOCList(
                    $item['children'],
                    $listing_type,
                    $expand_icon_type,
                    $default_expanded,
                    $expand_first_item,
                    $depth + 1,
                    false,
                    $is_child_hidden
                );

                $html .= $child_html;
            }

            if ($listing_type === 'none') {
                $html .= '</div>';
            } else {
                $html .= '</li>';
            }
        }

        $html .= sprintf('</%s>', $list_tag);

        return $html;
    }

    protected function getExpandIcon(string $type, bool $is_expanded): string
    {
        switch ($type) {
            case 'chevron':
                return $is_expanded ? '▼' : '▶';
            case 'arrow':
                return $is_expanded ? '↓' : '→';
            case 'caret':
                return $is_expanded ? '▾' : '▸';
            case 'plus-minus':
            default:
                return $is_expanded ? '−' : '+';
        }
    }

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $listing_type = $attributes['listingType'] ?? 'ul';
        $expand_icon_type = $attributes['expandIconType'] ?? 'plus-minus';
        $default_expanded = !empty($attributes['defaultExpanded']);
        $expand_first_item = !empty($attributes['expandFirstItem']);
        $show_heading = !empty($attributes['showHeading']);
        $hide_empty_message = !empty($attributes['hideEmptyMessage']);
        $custom_heading_text = $attributes['customHeadingText'] ?? '';
        $heading_style = $attributes['headingStyle'] ?? 'underline';
        $min_heading_level = absint($attributes['minHeadingLevel'] ?? 1);
        $max_heading_level = absint($attributes['maxHeadingLevel'] ?? 6);
        $bullet_type = $attributes['bulletType'] ?? 'disc';
        $hierarchical_indent = !empty($attributes['hierarchicalIndent']);
        $class_name = $attributes['className'] ?? '';
        $anchor = $attributes['anchor'] ?? '';

        $post = get_post();
        $post_content = '';

        if ($post && !empty($post->post_content)) {
            $post_content = $post->post_content;
        }

        $toc_items = $this->extractHeadings($post_content, $min_heading_level, $max_heading_level);

        if ($this->usesHTMLHeadings && !self::$contentFilterAdded) {
            add_filter('the_content', [$this, 'filterContentHeadings'], 10, 1);
            self::$contentFilterAdded = true;
        }

        $heading_text = !empty($custom_heading_text) ? $custom_heading_text : esc_html__('Table of Contents', 'timeless');

        if (empty($toc_items)) {
            $is_template_editor = $this->isTemplateEditor();

            if ($is_template_editor) {
                $toc_items = $this->getMockHeadings($min_heading_level, $max_heading_level);
            } else {
                $wrapper_class = 'jankx-table-of-content heading-style-' . esc_attr($heading_style) . ' has-bullet-' . esc_attr($bullet_type);
                if (!empty($class_name)) {
                    $wrapper_class .= ' ' . esc_attr($class_name);
                }
                $wrapper_attrs = get_block_wrapper_attributes([
                    'class' => $wrapper_class,
                    'id' => !empty($anchor) ? $anchor : null,
                ]);

                if (!empty(trim($content))) {
                    return sprintf(
                        '<div %s><nav class="toc-wrapper toc-wrapper--default-content" aria-label="%s">%s</nav></div>',
                        $wrapper_attrs,
                        esc_attr($heading_text),
                        $content
                    );
                }

                if ($hide_empty_message) {
                    return '';
                }

                return sprintf(
                    '<div %s><div class="toc-placeholder"><p>%s</p></div></div>',
                    $wrapper_attrs,
                    esc_html__('No headings found in the post content.', 'timeless')
                );
            }
        }

        $wrapper_class = 'jankx-table-of-content heading-style-' . esc_attr($heading_style) . ' has-bullet-' . esc_attr($bullet_type);
        if (!$hierarchical_indent) {
            $wrapper_class .= ' is-flat-hierarchy';
        }
        if (!empty($class_name)) {
            $wrapper_class .= ' ' . esc_attr($class_name);
        }

        $wrapper_attrs = get_block_wrapper_attributes([
            'class' => $wrapper_class,
            'data-default-expanded' => $default_expanded ? 'true' : 'false',
            'data-expand-first-item' => $expand_first_item ? 'true' : 'false',
            'data-expand-icon-type' => $expand_icon_type,
            'id' => !empty($anchor) ? $anchor : null,
        ]);

        $toc_html = $this->renderTOCList(
            $toc_items,
            $listing_type,
            $expand_icon_type,
            $default_expanded,
            $expand_first_item,
            0,
            true
        );

        $heading_html = '';
        if ($show_heading) {
            $heading_html = sprintf(
                '<div class="toc-header"><h2 class="toc-title">%s</h2></div>',
                esc_html($heading_text)
            );
        }

        return sprintf(
            '<div %s><nav class="toc-wrapper" aria-label="%s">%s%s</nav></div>',
            $wrapper_attrs,
            esc_attr($heading_text),
            $heading_html,
            $toc_html
        );
    }
}
