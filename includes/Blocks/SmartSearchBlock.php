<?php

namespace Timeless\Blocks;

use function absint;
use function admin_url;
use function apply_filters;
use function check_ajax_referer;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_url;
use function filter_var;
use function FILTER_VALIDATE_BOOLEAN;
use function get_avatar_url;
use function get_author_posts_url;
use function get_post_type_archive_link;
use function get_post_types;
use function get_search_query;
use function get_term_link;
use function get_terms;
use function get_the_excerpt;
use function get_the_title;
use function get_user_meta;
use function get_users;
use function is_array;
use function json_decode;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function sprintf;
use function stripslashes;
use function trim;
use function wp_create_nonce;
use function wp_send_json_success;
use function wp_trim_words;
use function wp_unslash;

class SmartSearchBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/smart-search';

    public function boot(): void
    {
        add_action('rest_api_init', [$this, 'registerRestEndpoints']);
        add_action('wp_ajax_timeless_smart_search_suggestions', [$this, 'handleSearchSuggestions']);
        add_action('wp_ajax_nopriv_timeless_smart_search_suggestions', [$this, 'handleSearchSuggestions']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
    }

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $placeholder = $attributes['placeholder'] ?? __('Search...', 'timeless');
        $showPostTypeFilter = $attributes['showPostTypeFilter'] ?? false;
        $postTypes = $attributes['postTypes'] ?? [];
        $showTaxonomyFilter = $attributes['showTaxonomyFilter'] ?? false;
        $taxonomies = $attributes['taxonomies'] ?? [];
        $enableAutoSuggestion = $attributes['enableAutoSuggestion'] ?? false;
        $showPosts = $attributes['showPosts'] ?? true;
        $showPostTypes = $attributes['showPostTypes'] ?? false;
        $showUsers = $attributes['showUsers'] ?? false;
        $showTaxonomy = $attributes['showTaxonomy'] ?? false;
        $showTags = $attributes['showTags'] ?? false;
        $suggestionLimit = (int) ($attributes['suggestionLimit'] ?? 10);
        $iconPosition = $attributes['iconPosition'] ?? 'left';
        $showIcon = $attributes['showIcon'] ?? true;
        $showLabel = $attributes['showLabel'] ?? true;
        $labelText = $attributes['labelText'] ?? '';
        $buttonPosition = $attributes['buttonPosition'] ?? 'right';
        $searchUrl = $attributes['searchUrl'] ?? '';

        $classes = array_filter([
            'jankx-smart-search',
            'search-icon-position-' . sanitize_html_class($iconPosition),
            'search-button-position-' . sanitize_html_class($buttonPosition),
        ]);

        $dataAttrs = [];
        $dataAttrs[] = sprintf('data-enable-auto-suggestion="%s"', $enableAutoSuggestion ? 'true' : 'false');
        $dataAttrs[] = sprintf('data-suggestion-limit="%d"', $suggestionLimit);
        $dataAttrs[] = sprintf('data-search-url="%s"', esc_url($searchUrl));

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" <?php echo implode(' ', $dataAttrs); ?>>
            <?php if ($showLabel && $labelText !== '') : ?>
                <label class="smart-search-label"><?php echo esc_html($labelText); ?></label>
            <?php endif; ?>

            <form class="smart-search-form" role="search" action="<?php echo esc_url($searchUrl !== '' ? $searchUrl : home_url('/')); ?>" method="get">
                <?php if ($showIcon) : ?>
                    <span class="smart-search-icon">&#x1f50d;</span>
                <?php endif; ?>

                <input
                    type="search"
                    name="s"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="smart-search-input"
                    autocomplete="off"
                />

                <?php if ($showPostTypeFilter && !empty($postTypes)) : ?>
                    <select name="post_type" class="smart-search-post-type-filter">
                        <?php foreach ($postTypes as $pt) : ?>
                            <option value="<?php echo esc_attr($pt); ?>"><?php echo esc_html(get_post_type_object($pt)->labels->singular_name ?? $pt); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php if ($enableAutoSuggestion) : ?>
                    <div class="smart-search-suggestions" style="display:none;"></div>
                <?php endif; ?>

                <button type="submit" class="smart-search-submit">
                    <?php _e('Search', 'timeless'); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    public function registerRestEndpoints(): void
    {
        register_rest_route('timeless/v1', '/smart-search/post-types', [
            'methods' => 'GET',
            'callback' => [$this, 'handleGetPostTypesRequest'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('timeless/v1', '/smart-search/taxonomies', [
            'methods' => 'GET',
            'callback' => [$this, 'handleGetTaxonomiesRequest'],
            'permission_callback' => '__return_true',
            'args' => [
                'post_type' => [
                    'required' => false,
                    'type' => 'string',
                ],
            ],
        ]);
    }

    public function enqueueFrontendAssets(): void
    {
        wp_enqueue_script(
            'timeless-smart-search-frontend',
            get_template_directory_uri() . '/resources/dist/blocks/smart-search/frontend.js',
            [],
            filemtime(get_template_directory() . '/resources/dist/blocks/smart-search/frontend.js') ?: false,
            true
        );

        wp_localize_script('timeless-smart-search-frontend', 'timelessSmartSearch', [
            'nonce' => wp_create_nonce('timeless_smart_search_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    public function handleSearchSuggestions(): void
    {
        check_ajax_referer('timeless_smart_search_nonce', 'nonce');

        $query = sanitize_text_field($_POST['query'] ?? '');
        if ($query === '') {
            wp_send_json_success([]);
        }

        $postTypes = ['post'];
        if (isset($_POST['post_types'])) {
            $decoded = json_decode(stripslashes($_POST['post_types']), true);
            if (is_array($decoded)) {
                $postTypes = $decoded;
            } elseif (is_array($_POST['post_types'])) {
                $postTypes = $_POST['post_types'];
            }
        }

        $taxonomies = [];
        if (isset($_POST['taxonomies'])) {
            $decoded = json_decode(stripslashes($_POST['taxonomies']), true);
            if (is_array($decoded)) {
                $taxonomies = $decoded;
            } elseif (is_array($_POST['taxonomies'])) {
                $taxonomies = $_POST['taxonomies'];
            }
        }

        $showPosts = filter_var($_POST['show_posts'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $showPostTypes = filter_var($_POST['show_post_types'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $showUsers = filter_var($_POST['show_users'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $showTaxonomy = filter_var($_POST['show_taxonomy'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $showTags = filter_var($_POST['show_tags'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $limit = absint($_POST['limit'] ?? 10);

        if ($query === '') {
            wp_send_json_success([]);
        }

        $results = [];
        $countEnabledTypes = 0;
        if ($showPosts && !empty($postTypes)) $countEnabledTypes++;
        if ($showPostTypes) $countEnabledTypes++;
        if ($showUsers) $countEnabledTypes++;
        if ($showTaxonomy && !empty($taxonomies)) $countEnabledTypes++;
        if ($showTags) $countEnabledTypes++;

        $limitPerType = $countEnabledTypes > 0 ? max(2, floor($limit / $countEnabledTypes)) : 10;

        if ($showPosts && !empty($postTypes)) {
            $postResults = $this->searchPosts($query, $postTypes, $limitPerType);
            if (!empty($postResults)) {
                $results['posts'] = $postResults;
            }
        }

        if ($showPostTypes) {
            $postTypeResults = $this->searchPostTypes($query, $limitPerType);
            if (!empty($postTypeResults)) {
                $results['post_types'] = $postTypeResults;
            }
        }

        if ($showUsers) {
            $userResults = $this->searchUsers($query, $limitPerType);
            if (!empty($userResults)) {
                $results['users'] = $userResults;
            }
        }

        if ($showTaxonomy && !empty($taxonomies)) {
            $taxonomyResults = $this->searchTaxonomyTerms($query, $taxonomies, $limitPerType);
            if (!empty($taxonomyResults)) {
                $results['taxonomies'] = $taxonomyResults;
            }
        }

        if ($showTags) {
            $tagResults = $this->searchTags($query, $limitPerType);
            if (!empty($tagResults)) {
                $results['tags'] = $tagResults;
            }
        }

        wp_send_json_success($results);
    }

    protected function searchPosts(string $query, array $postTypes, int $limit = 10): array
    {
        $args = [
            's' => $query,
            'post_type' => $postTypes,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'relevance',
            'order' => 'DESC',
        ];

        $searchQuery = new \WP_Query($args);
        $results = [];

        if ($searchQuery->have_posts()) {
            while ($searchQuery->have_posts()) {
                $searchQuery->the_post();
                $results[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'type' => get_post_type(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 15),
                ];
            }
            wp_reset_postdata();
        }

        return $results;
    }

    protected function searchPostTypes(string $query, int $limit = 10): array
    {
        $postTypes = get_post_types(['public' => true], 'objects');
        $results = [];

        foreach ($postTypes as $postType) {
            if (count($results) >= $limit) {
                break;
            }

            $name = $postType->name;
            $label = $postType->label;

            if (stripos($label, $query) !== false || stripos($name, $query) !== false) {
                $results[] = [
                    'name' => $name,
                    'label' => $label,
                    'url' => get_post_type_archive_link($name),
                ];
            }
        }

        return $results;
    }

    protected function searchUsers(string $query, int $limit = 10): array
    {
        $args = [
            'search' => '*' . $query . '*',
            'search_columns' => ['user_login', 'user_nicename', 'user_email', 'display_name'],
            'number' => $limit,
        ];

        $userQuery = new \WP_User_Query($args);
        $results = [];

        foreach ($userQuery->get_results() as $user) {
            $results[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'username' => $user->user_login,
                'url' => get_author_posts_url($user->ID),
                'avatar' => get_avatar_url($user->ID, ['size' => 32]),
            ];
        }

        return $results;
    }

    protected function searchTaxonomyTerms(string $query, array $taxonomies, int $limit = 10): array
    {
        $results = [];

        foreach ($taxonomies as $taxonomy) {
            $args = [
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'number' => $limit,
                'search' => $query,
            ];

            $terms = get_terms($args);

            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $results[] = [
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'taxonomy' => $taxonomy,
                        'url' => get_term_link($term),
                        'count' => $term->count,
                    ];
                }
            }
        }

        return $results;
    }

    protected function searchTags(string $query, int $limit = 10): array
    {
        $args = [
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
            'number' => $limit,
            'search' => $query,
        ];

        $terms = get_terms($args);
        $results = [];

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $results[] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link($term),
                    'count' => $term->count,
                ];
            }
        }

        return $results;
    }

    public function handleGetPostTypesRequest($request)
    {
        $postTypes = get_post_types(['public' => true], 'objects');
        $results = [];

        foreach ($postTypes as $postType) {
            if ($postType->name === 'attachment') {
                continue;
            }

            $taxonomies = get_object_taxonomies($postType->name, 'objects');
            $taxonomyList = [];

            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy->public && $taxonomy->name !== 'post_format') {
                    $taxonomyList[] = [
                        'name' => $taxonomy->name,
                        'label' => $taxonomy->labels->singular_name ?: $taxonomy->label,
                        'plural_label' => $taxonomy->label,
                    ];
                }
            }

            $results[] = [
                'name' => $postType->name,
                'label' => $postType->labels->singular_name ?: $postType->label,
                'plural_label' => $postType->label,
                'taxonomies' => $taxonomyList,
            ];
        }

        return rest_ensure_response($results);
    }

    public function handleGetTaxonomiesRequest($request)
    {
        $postType = $request->get_param('post_type');

        if ($postType) {
            $taxonomies = get_object_taxonomies($postType, 'objects');
        } else {
            $taxonomies = get_taxonomies(['public' => true], 'objects');
        }

        $results = [];

        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name === 'post_format') {
                continue;
            }

            $objectTypes = $taxonomy->object_type ?? [];
            $postTypesForTaxonomy = [];

            foreach ($objectTypes as $objectType) {
                $postTypeObj = get_post_type_object($objectType);
                if ($postTypeObj && $postTypeObj->public) {
                    $postTypesForTaxonomy[] = [
                        'name' => $postTypeObj->name,
                        'label' => $postTypeObj->labels->singular_name ?: $postTypeObj->label,
                    ];
                }
            }

            $results[] = [
                'name' => $taxonomy->name,
                'label' => $taxonomy->labels->singular_name ?: $taxonomy->label,
                'plural_label' => $taxonomy->label,
                'post_types' => $postTypesForTaxonomy,
            ];
        }

        return rest_ensure_response($results);
    }
}
