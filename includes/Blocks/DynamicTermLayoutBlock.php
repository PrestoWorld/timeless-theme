<?php

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_url;
use function get_queried_object;
use function is_array;
use function is_wp_error;
use function number_format_i18n;
use function paginate_links;
use function render_block;
use function sanitize_key;
use function sprintf;
use function substr_count;
use function taxonomy_exists;
use function wp_json_encode;
use function wp_reset_postdata;

class DynamicTermLayoutBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/dynamic-term-layout';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $taxonomy = $attributes['taxonomy'] ?? '';
        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return '';
        }

        $queryId = $attributes['queryId'] ?? '';
        if ($queryId === '') {
            $queryId = 'ddl-' . substr(md5(serialize($attributes)), 0, 10);
            $attributes['queryId'] = $queryId;
        }

        // Extract template block from inner blocks
        $templateBlock = null;
        if ($block instanceof \WP_Block && !empty($block->inner_blocks)) {
            foreach ($block->inner_blocks as $innerBlock) {
                if ($innerBlock->name === 'jankx/dynamic-term-template') {
                    $templateBlock = $innerBlock;
                    break;
                }
            }
        }

        // Build term query
        $args = [
            'taxonomy'   => $taxonomy,
            'hide_empty' => !empty($attributes['hideEmpty']),
            'number'     => (int) ($attributes['postsPerPage'] ?? 10),
            'orderby'    => $attributes['orderBy'] ?? 'name',
            'order'      => $attributes['order'] ?? 'ASC',
        ];

        if (!empty($attributes['termIn'])) {
            $args['include'] = is_array($attributes['termIn']) ? $attributes['termIn'] : [$attributes['termIn']];
        }
        if (!empty($attributes['termNotIn'])) {
            $args['exclude'] = is_array($attributes['termNotIn']) ? $attributes['termNotIn'] : [$attributes['termNotIn']];
        }
        if (!empty($attributes['termParent'])) {
            $args['parent'] = (int) $attributes['termParent'];
        }
        if (!empty($attributes['keyword'])) {
            $args['search'] = sanitize_key($attributes['keyword']);
        }

        $args = apply_filters('timeless_dynamic_term_layout_query_args', $args, $attributes);

        $terms = (new \WP_Term_Query($args))->get_terms();
        $hasResults = is_array($terms) && !empty($terms);

        // Apply render offset/limit
        if ($hasResults) {
            $renderOffset = (int) ($attributes['renderOffset'] ?? 0);
            $renderLimit = (int) ($attributes['renderLimit'] ?? 0);
            if ($renderOffset > 0 || $renderLimit > 0) {
                $terms = array_slice($terms, $renderOffset, $renderLimit > 0 ? $renderLimit : null);
            }
        }

        // Build wrapper attributes
        $wrapperClasses = ['wp-block-jankx-dynamic-term-layout'];
        if (!empty($attributes['className'])) {
            $wrapperClasses[] = esc_attr($attributes['className']);
        }
        if (!empty($attributes['layout'])) {
            $wrapperClasses[] = 'layout-' . esc_attr($attributes['layout']);
        }

        $dataAttrs = '';
        $dataAttrs .= sprintf(' data-block-id="%s"', esc_attr($queryId));
        $dataAttrs .= sprintf(' data-taxonomy="%s"', esc_attr($taxonomy));
        $dataAttrs .= sprintf(' data-layout="%s"', esc_attr($attributes['layout'] ?? ''));
        $dataAttrs .= sprintf(' data-block-settings="%s"', esc_attr(wp_json_encode($attributes)));

        $styleRules = [];
        $columns = (int) ($attributes['columns'] ?? 3);
        $styleRules[] = '--columns-desktop: ' . $columns;
        if (isset($attributes['columnsTablet'])) {
            $styleRules[] = '--columns-tablet: ' . (int) $attributes['columnsTablet'];
        }
        if (isset($attributes['columnsMobile'])) {
            $styleRules[] = '--columns-mobile: ' . (int) $attributes['columnsMobile'];
        }
        $wrapperStyle = $styleRules !== '' ? ' style="' . implode(';', $styleRules) . '"' : '';

        ob_start();
        ?>
        <div class="<?php echo implode(' ', $wrapperClasses); ?>"<?php echo $dataAttrs; ?><?php echo $wrapperStyle; ?>>

            <?php if ($hasResults) : ?>
                <?php foreach ($terms as $term) : ?>
                    <?php
                    if ($templateBlock) {
                        // Set up term context for template rendering
                        $termContext = [
                            'term' => $term,
                            'taxonomy' => $taxonomy,
                        ];
                        // Temporarily set global queried object for link resolution
                        global $wp_query;
                        $oldQueriedObject = $wp_query->get_queried_object();
                        $wp_query->set_queried_object($term);
                        echo $templateBlock->render();
                        $wp_query->set_queried_object($oldQueriedObject);
                    } else {
                        echo sprintf(
                            '<article class="jankx-ddl-term-item"><a href="%s">%s</a></article>',
                            esc_url(get_term_link($term, $taxonomy)),
                            esc_html($term->name)
                        );
                    }
                    ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="jankx-ddl-empty-state">
                    <?php echo esc_html($attributes['emptyMessage'] ?? __('No terms found.', 'timeless')); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
