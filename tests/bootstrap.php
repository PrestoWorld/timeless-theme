<?php

/**
 * Test bootstrap for Timeless theme block tests.
 *
 * Stubs WordPress core functions and classes so theme block files
 * can be loaded and tested without a full WordPress installation.
 */

// --- Functions ---

if (!function_exists('__')) {
    function __($text, $domain = 'default') { return $text; }
}
if (!function_exists('_e')) {
    function _e($text, $domain = 'default') { echo $text; }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) { return (string) $text; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return (string) $text; }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return (string) $url; }
}
if (!function_exists('get_template_directory')) {
    function get_template_directory() { return __DIR__ . '/..'; }
}
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() { return 'https://example.com/wp-content/themes/timeless'; }
}
if (!function_exists('wp_get_theme')) {
    function wp_get_theme() { return (object) ['get' => function($key) { return '1.0'; }]; }
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) { return $value; }
}
if (!function_exists('get_the_ID')) {
    function get_the_ID() { return 0; }
}
if (!function_exists('get_post')) {
    function get_post($id = null) { return null; }
}
if (!function_exists('get_queried_object')) {
    function get_queried_object() { return null; }
}
if (!function_exists('get_term')) {
    function get_term($term, $taxonomy = '') { return null; }
}
if (!function_exists('get_terms')) {
    function get_terms($args = []) { return []; }
}
if (!function_exists('taxonomy_exists')) {
    function taxonomy_exists($taxonomy) { return false; }
}
if (!function_exists('get_taxonomy')) {
    function get_taxonomy($taxonomy) { return null; }
}
if (!function_exists('get_block_wrapper_attributes')) {
    function get_block_wrapper_attributes($attributes = []) {
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($key === 'class' && is_array($value)) {
                $value = implode(' ', $value);
            }
            $html .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        return trim($html);
    }
}
if (!function_exists('parse_blocks')) {
    function parse_blocks($content) { return []; }
}
if (!function_exists('do_blocks')) {
    function do_blocks($content) { return $content; }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags($html) { return strip_tags($html); }
}
if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = []) { return array_merge($defaults, (array) $args); }
}
if (!function_exists('number_format_i18n')) {
    function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals); }
}
if (!function_exists('current_user_can')) {
    function current_user_can($capability) { return false; }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() { return false; }
}
if (!function_exists('get_current_user')) {
    function get_current_user() { return (object) ['ID' => 0, 'display_name' => 'User']; }
}
if (!function_exists('wp_logout_url')) {
    function wp_logout_url($redirect = '') { return $redirect; }
}
if (!function_exists('wp_login_url')) {
    function wp_login_url($redirect = '') { return $redirect; }
}
if (!function_exists('wp_registration_url')) {
    function wp_registration_url() { return ''; }
}
if (!function_exists('get_option')) {
    function get_option($name, $default = false) { return $default; }
}
if (!function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = []) { return '<img src="" alt="" />'; }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) { return $data; }
}
if (!function_exists('the_widget')) {
    function the_widget($widget, $instance = [], $args = []) {}
}
if (!function_exists('wp_list_categories')) {
    function wp_list_categories($args = []) { echo ''; }
}
if (!function_exists('wp_get_archives')) {
    function wp_get_archives($args = []) { echo ''; }
}
if (!function_exists('wp_dequeue_style')) {
    function wp_dequeue_style($handle) {}
}
if (!function_exists('wp_style_is')) {
    function wp_style_is($handle, $operation = 'registered') { return false; }
}
if (!function_exists('wp_script_is')) {
    function wp_script_is($handle, $operation = 'registered') { return false; }
}
if (!function_exists('register_block_type_from_metadata')) {
    function register_block_type_from_metadata($block_path, $args = []) { return null; }
}
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('rest_ensure_response')) {
    function rest_ensure_response($data) { return $data; }
}
if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = []) {}
}
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_var = false, $die = true) { return true; }
}
if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {}
}
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) { return 'nonce'; }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return 'https://example.com/wp-admin/'; }
}
if (!function_exists('home_url')) {
    function home_url($path = '') { return 'https://example.com/'; }
}
if (!function_exists('get_permalink')) {
    function get_permalink($post = null) { return 'https://example.com/'; }
}
if (!function_exists('get_post_type_object')) {
    function get_post_type_object($post_type) { return null; }
}
if (!function_exists('get_object_taxonomies')) {
    function get_object_taxonomies($post_type, $output = 'names') { return []; }
}
if (!function_exists('get_taxonomies')) {
    function get_taxonomies($args = [], $output = 'names') { return []; }
}
if (!function_exists('get_users')) {
    function get_users($args = []) { return []; }
}
if (!function_exists('get_avatar_url')) {
    function get_avatar_url($id_or_email, $args = []) { return ''; }
}
if (!function_exists('get_avatar')) {
    function get_avatar($id_or_email, $size = 96, $default = '', $alt = false) { return '<img src="" alt="" />'; }
}
if (!function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key = '', $single = false) { return ''; }
}
if (!function_exists('get_author_posts_url')) {
    function get_author_posts_url($author_id, $author_nicename = '') { return ''; }
}
if (!function_exists('get_post_type_archive_link')) {
    function get_post_type_archive_link($post_type = '') { return ''; }
}
if (!function_exists('get_term_link')) {
    function get_term_link($term, $taxonomy = '') { return ''; }
}
if (!function_exists('get_post_types')) {
    function get_post_types($args = [], $output = 'names', $operator = 'and') { return []; }
}
if (!function_exists('get_search_query')) {
    function get_search_query() { return ''; }
}
if (!function_exists('get_the_excerpt')) {
    function get_the_excerpt($post = null) { return ''; }
}
if (!function_exists('get_the_title')) {
    function get_the_title($post = null) { return ''; }
}
if (!function_exists('wp_trim_words')) {
    function wp_trim_words($text, $num_words = 55, $more = null) { return $text; }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return $str; }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return is_array($value) ? array_map('wp_unslash', $value) : stripslashes($value); }
}
if (!function_exists('filter_var')) {
    function filter_var($var, $filter = null, $options = []) { return $var; }
}
if (!function_exists('absint')) {
    function absint($maybeint) { return abs((int) $maybeint); }
}
if (!function_exists('intval')) {
    function intval($var, $base = 10) { return (int) $var; }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return $key; }
}
if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($class, $fallback = '') { return $class; }
}
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data) { return json_encode($data); }
}
if (!function_exists('safecss_filter_attr')) {
    function safecss_filter_attr($css) { return $css; }
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) { return $thing instanceof \WP_Error; }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) { return strncmp($haystack, $needle, strlen($needle)) === 0; }
}

// --- Classes ---

if (!class_exists('WP_Block')) {
    class WP_Block {
        public $name = '';
        public $attrs = [];
        public $inner_blocks = [];
        public $context = [];
        public function render() { return ''; }
    }
}
if (!class_exists('WP_Block_Type')) {
    class WP_Block_Type {
        public $style = null;
        public $view_script = null;
    }
}
if (!class_exists('WP_Term')) {
    class WP_Term {
        public $term_id = 0;
        public $taxonomy = '';
        public $name = '';
        public $slug = '';
        public $count = 0;
        public $labels = [];

        public function __construct() {
            $this->labels = (object) [];
        }
    }
}
if (!class_exists('WP_Query')) {
    class WP_Query {
        public $posts = [];
        public $found_posts = 0;
        public $max_num_pages = 1;
        public function __construct($args = []) {}
        public function have_posts() { return false; }
        public function the_post() {}
        public function get_results() { return []; }
    }
}
if (!class_exists('WP_User_Query')) {
    class WP_User_Query {
        public function __construct($args = []) {}
        public function get_results() { return []; }
    }
}
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        public function get_param($key) { return null; }
    }
}
if (!class_exists('WP_REST_Server')) {
    class WP_REST_Server {
        const READABLE = 'READABLE';
    }
}
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        public function __construct($data, $status = 200) {}
    }
}
if (!class_exists('WP_Error')) {
    class WP_Error extends \Exception {}
}

if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}
