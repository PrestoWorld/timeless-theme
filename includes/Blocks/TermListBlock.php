<?php

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function get_taxonomy;
use function get_terms;
use function in_array;
use function is_wp_error;
use function sanitize_html_class;
use function sanitize_key;
use function sprintf;
use function strtoupper;
use function taxonomy_exists;

class TermListBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/term-list';

    protected array $allowedLayouts = ['list', 'grid', 'flex'];

    protected array $allowedOrderBy = ['name', 'slug', 'count', 'term_order', 'description'];

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $taxonomySlug = isset($attributes['taxonomy']) ? sanitize_key($attributes['taxonomy']) : '';

        if (empty($taxonomySlug) || !taxonomy_exists($taxonomySlug)) {
            return '';
        }

        $taxonomy = get_taxonomy($taxonomySlug);
        if (!$taxonomy) {
            return '';
        }

        $attributes = $this->normalizeAttributes($attributes);
        $layout = $attributes['layout'];

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'jankx-term-list jankx-term-list--' . sanitize_html_class($layout),
        ]);

        $terms = $this->getTerms($attributes, $taxonomySlug);

        if (empty($terms) || is_wp_error($terms)) {
            return sprintf(
                '<div %s><div class="jankx-term-list__empty">%s</div></div>',
                $wrapperAttributes,
                esc_html($taxonomy->labels->no_terms ?? __('No terms', 'timeless'))
            );
        }

        $items = [];
        foreach ($terms as $term) {
            $termLink = get_term_link($term);
            if (is_wp_error($termLink)) {
                continue;
            }

            $countHtml = '';
            if (!empty($attributes['showTermCount'])) {
                $countHtml = sprintf(' <span class="jankx-term-list__count">%d</span>', (int) $term->count);
            }

            $items[] = sprintf(
                '<li class="jankx-term-list__item"><a href="%s">%s</a>%s</li>',
                esc_url($termLink),
                esc_html($term->name),
                $countHtml
            );
        }

        $itemsHtml = implode('', $items);

        if ($itemsHtml === '') {
            return sprintf(
                '<div %s><div class="jankx-term-list__empty">%s</div></div>',
                $wrapperAttributes,
                esc_html($taxonomy->labels->no_terms ?? __('No terms', 'timeless'))
            );
        }

        $listTag = $layout === 'list' ? 'ul' : 'div';
        $listClass = 'jankx-term-list__items jankx-term-list__items--' . sanitize_html_class($layout);

        return sprintf(
            '<div %s><%s class="%s">%s</%s></div>',
            $wrapperAttributes,
            $listTag,
            $listClass,
            $itemsHtml,
            $listTag
        );
    }

    protected function normalizeAttributes(array $attributes): array
    {
        $layout = isset($attributes['layout']) ? (string) $attributes['layout'] : 'list';
        if (!in_array($layout, $this->allowedLayouts, true)) {
            $layout = 'list';
        }

        $orderBy = isset($attributes['orderBy']) ? (string) $attributes['orderBy'] : 'name';
        if (!in_array($orderBy, $this->allowedOrderBy, true)) {
            $orderBy = 'name';
        }

        $order = isset($attributes['order']) ? strtoupper((string) $attributes['order']) : 'ASC';
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'ASC';
        }

        return array_merge($attributes, [
            'layout' => $layout,
            'orderBy' => $orderBy,
            'order' => $order,
            'postsPerPage' => max(0, (int) (isset($attributes['postsPerPage']) ? $attributes['postsPerPage'] : 0)),
            'columns' => max(1, (int) (isset($attributes['columns']) ? $attributes['columns'] : 3)),
            'hideEmpty' => !empty($attributes['hideEmpty']),
            'showOnlyTopLevel' => !empty($attributes['showOnlyTopLevel']),
            'showTermCount' => !empty($attributes['showTermCount']),
        ]);
    }

    protected function getTerms(array $attributes, string $taxonomySlug)
    {
        $args = [
            'taxonomy' => $taxonomySlug,
            'hide_empty' => !empty($attributes['hideEmpty']),
            'orderby' => $attributes['orderBy'],
            'order' => $attributes['order'],
        ];

        if (!empty($attributes['showOnlyTopLevel'])) {
            $args['parent'] = 0;
        }

        if ($attributes['postsPerPage'] > 0) {
            $args['number'] = $attributes['postsPerPage'];
        }

        $args = apply_filters('timeless/term-list/query_args', $args, $attributes);

        return get_terms($args);
    }
}
