<?php

namespace Timeless\Blocks;

use function apply_filters;
use function class_exists;
use function esc_attr;
use function esc_html;
use function esc_url;
use function explode;
use function function_exists;
use function get_bloginfo;
use function get_category;
use function get_category_link;
use function get_queried_object;
use function get_permalink;
use function get_post;
use function get_post_ancestors;
use function get_post_type;
use function get_post_type_archive_link;
use function get_post_type_object;
use function get_search_query;
use function get_term;
use function get_term_link;
use function get_the_category;
use function get_the_date;
use function get_the_title;
use function home_url;
use function implode;
use function in_array;
use function is_404;
use function is_archive;
use function is_author;
use function is_category;
use function is_date;
use function is_day;
use function is_front_page;
use function is_home;
use function is_month;
use function is_page;
use function is_search;
use function is_single;
use function is_tag;
use function is_tax;
use function is_wp_error;
use function is_year;
use function sanitize_html_class;
use function single_term_title;
use function sprintf;
use function strlen;
use function strpos;
use function wp_style_engine_get_styles;

class SmartBreadcrumbBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/smart-breadcrumb';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $showHome = $attributes['showHome'] ?? true;
        $homeText = $attributes['homeText'] ?? __('Home', 'timeless');
        $separator = $attributes['separator'] ?? '›';
        $showCurrent = $attributes['showCurrent'] ?? true;
        $maxDepth = (int) ($attributes['maxDepth'] ?? 3);
        $stylePreset = $attributes['stylePreset'] ?? 'default';
        $useSeoPlugin = $attributes['useSeoPlugin'] ?? true;
        $fallbackToCustom = $attributes['fallbackToCustom'] ?? true;

        $breadcrumbHtml = '';

        if ($useSeoPlugin) {
            $breadcrumbHtml = $this->getSeoPluginBreadcrumb();
        }

        if (empty($breadcrumbHtml) && $fallbackToCustom) {
            $breadcrumbHtml = $this->generateCustomBreadcrumb($attributes);
        }

        if (empty($breadcrumbHtml)) {
            return '<p>' . esc_html__('No breadcrumb available', 'timeless') . '</p>';
        }

        $classes = ['wp-block-jankx-smart-breadcrumb'];
        $inlineStyles = [];

        if ($stylePreset !== '' && $stylePreset !== 'default') {
            $classes[] = 'breadcrumb-style-' . sanitize_html_class($stylePreset);
        }

        if (function_exists('wp_style_engine_get_styles')) {
            $styleAttributes = wp_style_engine_get_styles($attributes, [
                'selector' => '.wp-block-jankx-smart-breadcrumb',
                'context' => 'block-supports',
            ]);

            if (!empty($styleAttributes['classnames'])) {
                $classes = array_merge($classes, explode(' ', $styleAttributes['classnames']));
            }

            if (!empty($styleAttributes['css'])) {
                $inlineStyles[] = $styleAttributes['css'];
            }
        }

        $classAttr = implode(' ', array_unique($classes));
        $styleAttr = !empty($inlineStyles) ? ' style="' . esc_attr(implode('; ', $inlineStyles)) . '"' : '';

        return sprintf(
            '<nav class="%s"%s role="navigation" aria-label="%s">%s</nav>',
            esc_attr($classAttr),
            $styleAttr,
            esc_attr__('Breadcrumb', 'timeless'),
            $breadcrumbHtml
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function getSeoPluginBreadcrumb(): string
    {
        if (class_exists('RankMath') && function_exists('rank_math_the_breadcrumbs')) {
            ob_start();
            rank_math_the_breadcrumbs();
            return ob_get_clean();
        }

        if (class_exists('WPSEO_Breadcrumbs') && function_exists('yoast_breadcrumb')) {
            return yoast_breadcrumb('', '', false);
        }

        if (function_exists('seopress_display_breadcrumbs')) {
            ob_start();
            seopress_display_breadcrumbs();
            return ob_get_clean();
        }

        if (function_exists('bcn_display')) {
            ob_start();
            bcn_display();
            return ob_get_clean();
        }

        if (class_exists('WooCommerce') && function_exists('woocommerce_breadcrumb')) {
            ob_start();
            woocommerce_breadcrumb([
                'delimiter'   => ' › ',
                'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'timeless') . '">',
                'wrap_after'  => '</nav>',
                'before'      => '',
                'after'       => '',
                'home'        => _x('Home', 'breadcrumb', 'timeless'),
            ]);
            return ob_get_clean();
        }

        return '';
    }

    private function generateCustomBreadcrumb(array $attributes): string
    {
        $showHome = $attributes['showHome'] ?? true;
        $homeText = $attributes['homeText'] ?? __('Home', 'timeless');
        $separator = $attributes['separator'] ?? '›';
        $showCurrent = $attributes['showCurrent'] ?? true;
        $maxDepth = (int) ($attributes['maxDepth'] ?? 3);

        $breadcrumbItems = [];

        if ($showHome) {
            $breadcrumbItems[] = sprintf(
                '<a href="%s" class="home-item">%s</a>',
                esc_url(home_url('/')),
                esc_html($homeText)
            );
        }

        $queriedObject = get_queriedObject();
        $currentPage = get_post();

        $isEditorPreview = (defined('REST_REQUEST') && REST_REQUEST)
            || (!$currentPage && !$queriedObject)
            || (is_admin() && !wp_doing_ajax());

        if ($isEditorPreview) {
            $breadcrumbItems[] = '<a href="#">' . esc_html__('Sample Category', 'timeless') . '</a>';
            if ($showCurrent) {
                $breadcrumbItems[] = '<span class="current">' . esc_html__('Sample Post Title', 'timeless') . '</span>';
            }
        } elseif (is_home() || is_frontPage()) {
            // Home page - no additional items needed
        } elseif (is_category() || is_tag() || is_tax()) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->getTaxonomyBreadcrumb($queriedObject, $maxDepth));
        } elseif (is_single()) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->getPostBreadcrumb($currentPage, $maxDepth));
        } elseif (is_page()) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->getPageBreadcrumb($currentPage, $maxDepth));
        } elseif (is_archive()) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->getArchiveBreadcrumb($queriedObject, $maxDepth));
        } elseif (is_search()) {
            $breadcrumbItems[] = '<span>' . sprintf(__('Search Results for: "%s"', 'timeless'), get_search_query()) . '</span>';
        } elseif (is_404()) {
            $breadcrumbItems[] = '<span>' . esc_html__('Page not found', 'timeless') . '</span>';
        }

        if (!$isEditorPreview && $showCurrent && !empty($breadcrumbItems)) {
            $currentTitle = $this->getCurrentPageTitle();
            if ($currentTitle !== '') {
                $breadcrumbItems[] = '<span class="current">' . esc_html($currentTitle) . '</span>';
            }
        }

        $separatorHtml = '<span class="separator" aria-hidden="true">' . esc_html($separator) . '</span>';
        return implode($separatorHtml, $breadcrumbItems);
    }

    private function getTaxonomyBreadcrumb($term, int $maxDepth): array
    {
        $items = [];

        if ($term->parent && $maxDepth > 1) {
            $parentTerms = get_ancestors($term->term_id, $term->taxonomy);
            $parentTerms = array_reverse($parentTerms);

            foreach ($parentTerms as $parentId) {
                $parentTerm = get_term($parentId, $term->taxonomy);
                if ($parentTerm && !is_wp_error($parentTerm)) {
                    $items[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url(get_term_link($parentTerm)),
                        esc_html($parentTerm->name)
                    );
                }
            }
        }

        return $items;
    }

    private function getPostBreadcrumb($post, int $maxDepth): array
    {
        $items = [];

        $postType = get_post_type($post);
        if ($postType !== 'post' && $postType !== 'page') {
            $postTypeObj = get_post_type_object($postType);
            if ($postTypeObj && $postTypeObj->has_archive) {
                $archiveLink = get_post_type_archive_link($postType);
                if ($archiveLink) {
                    $items[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url($archiveLink),
                        esc_html($postTypeObj->labels->name)
                    );
                }
            }
        }

        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $category = $categories[0];
            if ($category->parent && $maxDepth > 1) {
                $parentCategories = get_ancestors($category->term_id, 'category');
                $parentCategories = array_reverse($parentCategories);

                foreach ($parentCategories as $parentId) {
                    $parentCategory = get_category($parentId);
                    if ($parentCategory && !is_wp_error($parentCategory)) {
                        $items[] = sprintf(
                            '<a href="%s">%s</a>',
                            esc_url(get_category_link($parentCategory)),
                            esc_html($parentCategory->name)
                        );
                    }
                }
            }
            $items[] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(get_category_link($category)),
                esc_html($category->name)
            );
        }

        return $items;
    }

    private function getPageBreadcrumb($post, int $maxDepth): array
    {
        $items = [];

        if ($post->post_parent && $maxDepth > 1) {
            $ancestors = get_post_ancestors($post->ID);
            $ancestors = array_reverse($ancestors);

            foreach ($ancestors as $ancestorId) {
                $ancestor = get_post($ancestorId);
                if ($ancestor) {
                    $items[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url(get_permalink($ancestor)),
                        esc_html($ancestor->post_title)
                    );
                }
            }
        }

        return $items;
    }

    private function getArchiveBreadcrumb($queriedObject, int $maxDepth): array
    {
        $items = [];

        if (is_author()) {
            $items[] = '<span>' . sprintf(__('Author: %s', 'timeless'), $queriedObject->display_name) . '</span>';
        } elseif (is_date()) {
            if (is_year()) {
                $items[] = '<span>' . get_the_date('Y') . '</span>';
            } elseif (is_month()) {
                $items[] = sprintf('<a href="%s">%s</a>', esc_url(get_year_link(get_the_date('Y'))), get_the_date('Y'));
                $items[] = '<span>' . get_the_date('F') . '</span>';
            } elseif (is_day()) {
                $items[] = sprintf('<a href="%s">%s</a>', esc_url(get_year_link(get_the_date('Y'))), get_the_date('Y'));
                $items[] = sprintf('<a href="%s">%s</a>', esc_url(get_month_link(get_the_date('Y'), get_the_date('m'))), get_the_date('F'));
                $items[] = '<span>' . get_the_date('j') . '</span>';
            }
        }

        return $items;
    }

    private function getCurrentPageTitle(): string
    {
        if (is_home() || is_frontPage()) {
            return get_bloginfo('name');
        } elseif (is_category() || is_tag() || is_tax()) {
            return single_term_title('', false);
        } elseif (is_single() || is_page()) {
            return get_the_title();
        } elseif (is_author()) {
            return get_the_author();
        } elseif (is_date()) {
            if (is_year()) {
                return get_the_date('Y');
            } elseif (is_month()) {
                return get_the_date('F Y');
            } elseif (is_day()) {
                return get_the_date();
            }
        } elseif (is_search()) {
            return sprintf(__('Search Results for: "%s"', 'timeless'), get_search_query());
        } elseif (is_404()) {
            return esc_html__('Page not found', 'timeless');
        }

        return '';
    }

    private function renderHomeItem(array $attributes): string
    {
        $type = $attributes['homeItemType'] ?? 'text';
        $text = $attributes['homeItemText'] ?? __('Home', 'timeless');
        $showText = $attributes['showHomeText'] ?? true;

        if (isset($attributes['homeText']) && $text === __('Trang chủ', 'timeless')) {
            $text = $attributes['homeText'];
        }

        $content = '';
        switch ($type) {
            case 'css':
                $iconClass = $attributes['homeItemIcon'] ?? 'fa fa-home';
                $content = sprintf('<i class="%s" aria-hidden="true"></i>', esc_attr($iconClass));
                break;
            case 'svg':
                $content = $attributes['homeItemSvg'] ?? '';
                break;
            case 'image':
                $image = $attributes['homeItemImage'] ?? null;
                if ($image && !empty($image['url'])) {
                    $content = sprintf(
                        '<img src="%s" alt="%s" class="home-icon" />',
                        esc_url($image['url']),
                        esc_attr($text)
                    );
                }
                break;
            case 'text':
            default:
                return esc_html($text);
        }

        if ($showText) {
            $content .= sprintf('<span class="home-text">%s</span>', esc_html($text));
        }

        return $content;
    }
}
