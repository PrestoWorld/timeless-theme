<?php

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_url;
use function get_post_statuses;
use function is_array;
use function is_wp_error;
use function number_format_i18n;
use function paginate_links;
use function render_block;
use function sanitize_key;
use function sprintf;
use function substr_count;
use function wp_json_encode;
use function wp_reset_postdata;
use function wp_trim_words;

class DynamicDataLayoutBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/dynamic-data-layout';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $queryId = $attributes['queryId'] ?? '';
        if ($queryId === '') {
            $queryId = 'ddl-' . substr(md5(serialize($attributes)), 0, 10);
            $attributes['queryId'] = $queryId;
        }

        // Extract template block from inner blocks
        $templateBlock = null;
        $innerBlocks = [];
        $headingBlock = null;

        if ($block instanceof \WP_Block && !empty($block->inner_blocks)) {
            foreach ($block->inner_blocks as $innerBlock) {
                $blockName = $innerBlock->name;
                if ($blockName === 'jankx/dynamic-data-template') {
                    $templateBlock = $innerBlock;
                } elseif ($blockName === 'core/heading') {
                    $headingBlock = $innerBlock;
                } else {
                    $innerBlocks[] = $innerBlock;
                }
            }
        }

        // Build query
        $query = $this->buildQuery($attributes);
        $hasResults = !$query instanceof \WP_Error && $query->have_posts();

        // Build wrapper attributes
        $wrapperClasses = ['wp-block-jankx-dynamic-data-layout'];
        if (!empty($attributes['className'])) {
            $wrapperClasses[] = esc_attr($attributes['className']);
        }
        if (!empty($attributes['layout'])) {
            $wrapperClasses[] = 'layout-' . esc_attr($attributes['layout']);
        }

        $dataAttrs = '';
        $dataAttrs .= sprintf(' data-block-id="%s"', esc_attr($queryId));
        $dataAttrs .= sprintf(' data-post-type="%s"', esc_attr($attributes['postType'] ?? ''));
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
        if (isset($attributes['spaceBetween'])) {
            $styleRules[] = '--space-between: ' . (int) $attributes['spaceBetween'] . 'px';
        }

        $wrapperStyle = $styleRules !== '' ? ' style="' . implode(';', $styleRules) . '"' : '';

        ob_start();
        ?>
        <div class="<?php echo implode(' ', $wrapperClasses); ?>"<?php echo $dataAttrs; ?><?php echo $wrapperStyle; ?>>

            <?php if ($headingBlock && $hasResults) : ?>
                <?php echo $headingBlock->render(); ?>
            <?php endif; ?>

            <?php if ($hasResults) : ?>
                <?php
                $postCount = 0;
                $postsPerPage = (int) ($attributes['postsPerPage'] ?? 10);
                $offset = (int) ($attributes['offset'] ?? 0);
                $renderLimit = (int) ($attributes['renderLimit'] ?? 0);
                $renderOffset = (int) ($attributes['renderOffset'] ?? 0);

                while ($query->have_posts() && ($renderLimit === 0 || $postCount < $renderLimit)) {
                    $query->the_post();
                    $postCount++;

                    if ($postCount <= $renderOffset) {
                        continue;
                    }

                    if ($templateBlock) {
                        echo $templateBlock->render();
                    } else {
                        echo $this->renderDefaultItem($query->post);
                    }
                }
                wp_reset_postdata();
                ?>
            <?php else : ?>
                <div class="jankx-ddl-empty-state">
                    <?php echo esc_html($attributes['emptyMessage'] ?? __('No posts found.', 'timeless')); ?>
                </div>
            <?php endif; ?>

            <?php
            if ($hasResults && !empty($attributes['enablePagination']) && $query->max_num_pages > 1) :
                $paged = max(1, (int) ($attributes['offset'] ?? 1));
                echo paginate_links([
                    'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'format'       => '?paged=%#%',
                    'current'      => $paged,
                    'total'        => $query->max_num_pages,
                    'prev_text'    => $attributes['paginationPrevText'] ?? __('Previous', 'timeless'),
                    'next_text'    => $attributes['paginationNextText'] ?? __('Next', 'timeless'),
                    'type'         => 'plain',
                    'end_size'     => 3,
                    'mid_size'     => 3,
                ]);
            endif;
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function buildQuery(array $attributes): \WP_Query|\WP_Error
    {
        $postType = $attributes['postType'] ?? 'post';
        $postsPerPage = (int) ($attributes['postsPerPage'] ?? 10);
        $offset = (int) ($attributes['offset'] ?? 0);
        $orderBy = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'DESC';
        $postStatus = $attributes['postStatus'] ?? 'publish';

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postsPerPage,
            'offset'         => $offset,
            'orderby'        => $orderBy,
            'order'          => $order,
            'post_status'    => $postStatus,
        ];

        // Tax query
        if (!empty($attributes['taxQuery']) && is_array($attributes['taxQuery'])) {
            $args['tax_query'] = $attributes['taxQuery'];
        }

        // Meta query
        if (!empty($attributes['metaQuery']) && is_array($attributes['metaQuery'])) {
            $args['meta_query'] = $attributes['metaQuery'];
        }

        // Keyword search
        if (!empty($attributes['keyword'])) {
            $args['s'] = sanitize_key($attributes['keyword']);
        }

        // Author filter
        if (!empty($attributes['authorIn'])) {
            $args['author__in'] = is_array($attributes['authorIn']) ? $attributes['authorIn'] : [$attributes['authorIn']];
        }
        if (!empty($attributes['authorNotIn'])) {
            $args['author__not_in'] = is_array($attributes['authorNotIn']) ? $attributes['authorNotIn'] : [$attributes['authorNotIn']];
        }

        // Post inclusion/exclusion
        if (!empty($attributes['postIn'])) {
            $args['post__in'] = is_array($attributes['postIn']) ? $attributes['postIn'] : [$attributes['postIn']];
        }
        if (!empty($attributes['postNotIn'])) {
            $args['post__not_in'] = is_array($attributes['postNotIn']) ? $attributes['postNotIn'] : [$attributes['postNotIn']];
        }

        // Sticky posts
        if (empty($attributes['includeStickyPosts'])) {
            $args['ignore_sticky_posts'] = true;
        }

        // Meta key ordering
        if (!empty($attributes['metaKey'])) {
            $args['meta_key'] = $attributes['metaKey'];
            if (!empty($attributes['metaType'])) {
                $args['meta_type'] = $attributes['metaType'];
            }
        }

        // Post parent
        if (!empty($attributes['postParent'])) {
            $args['post_parent'] = (int) $attributes['postParent'];
        }
        if (!empty($attributes['postParentIn'])) {
            $args['post_parent__in'] = is_array($attributes['postParentIn']) ? $attributes['postParentIn'] : [$attributes['postParentIn']];
        }
        if (!empty($attributes['postParentNotIn'])) {
            $args['post_parent__not_in'] = is_array($attributes['postParentNotIn']) ? $attributes['postParentNotIn'] : [$attributes['postParentNotIn']];
        }

        return apply_filters('timeless_dynamic_data_layout_query_args', $args, $attributes);
    }

    private function renderDefaultItem(\WP_Post $post): string
    {
        $thumbnail = '';
        if (has_post_thumbnail($post->ID)) {
            $thumbnail = get_the_post_thumbnail($post->ID, 'medium', ['class' => 'jankx-ddl-item-thumbnail']);
        }

        $title = sprintf(
            '<h3 class="jankx-ddl-item-title"><a href="%s">%s</a></h3>',
            esc_url(get_permalink($post->ID)),
            esc_html($post->post_title)
        );

        $excerpt = '';
        if ($post->post_excerpt !== '') {
            $excerpt = sprintf(
                '<div class="jankx-ddl-item-excerpt">%s</div>',
                wp_trim_words($post->post_excerpt, 20)
            );
        }

        return sprintf(
            '<article class="jankx-ddl-item">%s%s%s</article>',
            $thumbnail,
            $title,
            $excerpt
        );
    }
}
