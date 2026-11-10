<?php

namespace Timeless\Blocks;

use function add_action;
use function add_filter;
use function count;
use function esc_attr;
use function esc_url;
use function get_permalink;
use function get_post;
use function get_query_var;
use function get_template;
use function get_stylesheet;
use function in_array;
use function is_admin;
use function ob_get_clean;
use function ob_start;
use function parse_blocks;
use function paginate_links;
use function str_replace;
use function the_widget;
use function wp_dequeue_style;
use function wp_list_categories;
use function wp_get_archives;

class WordPressBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/wordpress';

    protected static array $calledComponents = [];

    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'handleGlobalAssetsTrigger'], 20);
    }

    public function handleGlobalAssetsTrigger(): void
    {
        if (is_admin()) {
            return;
        }

        global $post;
        if (!$post || empty($post->post_content)) {
            return;
        }

        $blocks = parse_blocks($post->post_content);
        if ($this->hasDisableGlobalStyleTrigger($blocks)) {
            wp_dequeue_style(get_template());
            wp_dequeue_style(get_stylesheet());
        }
    }

    protected function hasDisableGlobalStyleTrigger(array $blocks): bool
    {
        foreach ($blocks as $block) {
            if ($block['blockName'] === $this->blockId) {
                if (!empty($block['attrs']['disableGlobalStyle'])) {
                    return true;
                }
            }
            if (!empty($block['innerBlocks'])) {
                if ($this->hasDisableGlobalStyleTrigger($block['innerBlocks'])) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $type = $attributes['legacyType'] ?? 'none';

        if ($type !== 'none' && !in_array($type, self::$calledComponents, true)) {
            self::$calledComponents[] = $type;
            self::enqueueLegacyAssets($type);
        }

        $output = '';

        switch ($type) {
            case 'recent_comments':
                $output = $this->renderRecentComments($attributes);
                break;
            case 'categories':
                $output = $this->renderCategories($attributes);
                break;
            case 'archives':
                $output = $this->renderArchives($attributes);
                break;
            case 'pagination':
                $output = $this->renderPagination($attributes);
                break;
            case 'meta':
                $output = $this->renderMeta();
                break;
            default:
                $output = !empty($content) ? $content : '<!-- Empty Legacy Block -->';
                break;
        }

        return sprintf(
            '<div class="jankx-wordpress-legacy jankx-legacy-%s">%s</div>',
            esc_attr($type),
            $output
        );
    }

    public static function enqueueLegacyAssets(string $type): void
    {
        $handle = "timeless-legacy-{$type}";
        $css_content = self::getLegacyComponentCSS($type);

        if (!empty($css_content)) {
            add_action('wp_footer', function () use ($handle, $css_content) {
                echo "<style id='{$handle}-inline-css'>{$css_content}</style>";
            }, 5);
        }
    }

    public static function getLegacyComponentCSS(string $type): string
    {
        $css = [
            'categories' => '.wp-legacy-categories { list-style: none; padding: 0; } .wp-legacy-categories li { margin-bottom: 5px; }',
            'archives' => '.wp-legacy-archives { list-style: none; padding: 0; }',
            'recent_comments' => '.widget_recent_comments ul { list-style: none; padding: 0; }',
            'pagination' => '.wp-block-query-pagination { display: flex; align-items: center; justify-content: center; gap: 1rem; margin: 2rem 0; } .wp-block-query-pagination ul.page-numbers { display: flex; list-style: none; padding: 0; margin: 0; gap: 0.5rem; flex-wrap: wrap; justify-content: center; } .page-numbers:not(ul) { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 5px 10px; border: 1px solid #e0e0e0; border-radius: 4px; background: white; color: #333; text-decoration: none; font-size: 14px; transition: all 0.2s; } .page-numbers.current { background: var(--wp--preset--color--primary, #184962); border-color: var(--wp--preset--color--primary, #184962); color: white; } .page-numbers:hover:not(.current) { border-color: var(--wp--preset--color--primary, #184962); color: var(--wp--preset--color--primary, #184962); }',
        ];

        return $css[$type] ?? '';
    }

    protected function renderRecentComments(array $attrs): string
    {
        ob_start();
        the_widget('WP_Widget_Recent_Comments', $attrs['extraParams'] ?? []);
        return ob_get_clean();
    }

    protected function renderCategories(array $attrs): string
    {
        ob_start();
        echo '<ul class="wp-legacy-categories">';
        wp_list_categories(array_merge([
            'title_li' => '',
            'show_count' => true
        ], $attrs['extraParams'] ?? []));
        echo '</ul>';
        return ob_get_clean();
    }

    protected function renderArchives(array $attrs): string
    {
        ob_start();
        echo '<ul class="wp-legacy-archives">';
        wp_get_archives(array_merge([
            'type' => 'monthly',
            'show_post_count' => true
        ], $attrs['extraParams'] ?? []));
        echo '</ul>';
        return ob_get_clean();
    }

    protected function renderMeta(): string
    {
        ob_start();
        the_widget('WP_Widget_Meta');
        return ob_get_clean();
    }

    protected function renderPagination(array $attrs): string
    {
        global $wp_query;
        $big = 999999999;

        $args = array_merge([
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'type' => 'plain',
            'prev_text' => __('&larr; Previous', 'timeless'),
            'next_text' => __('Next &rarr;', 'timeless'),
        ], $attrs['extraParams'] ?? []);

        $links = paginate_links($args);

        if (!$links) {
            return '';
        }

        return sprintf('<nav class="wp-block-query-pagination">%s</nav>', $links);
    }
}
