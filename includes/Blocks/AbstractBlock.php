<?php
/**
 * Abstract Block Base Class
 *
 * Base class for all Gutenberg block implementations. Follows the
 * Jankx Gutenberg Block pattern: uses register_block_type_from_metadata()
 * to read block.json directly, with optional render_callback for SSR.
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_url;
use function file_exists;
use function is_admin;
use function is_readable;
use function register_block_type_from_metadata;
use function sprintf;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_get_theme;
use function wp_register_script;
use function wp_register_style;

/**
 * Abstract class AbstractBlock
 */
abstract class AbstractBlock implements BlockRendererInterface
{
    /**
     * Block ID (e.g. jankx/advanced-button)
     */
    protected string $blockId = '';

    /**
     * Path to block directory containing block.json
     */
    protected string $blockPath = '';

    /**
     * Whether the block has been registered
     */
    private bool $registered = false;

    /**
     * AbstractBlock constructor.
     */
    public function __construct(string $blockId = '', string $blockPath = '')
    {
        $this->blockId = $blockId;
        $this->blockPath = rtrim($blockPath, '/');
    }

    /**
     * Get block ID
     */
    public function getBlockId(): string
    {
        return $this->blockId;
    }

    /**
     * Get block directory path
     */
    public function getBlockPath(): string
    {
        return $this->blockPath;
    }

    /**
     * Set block path
     */
    public function setBlockPath(string $path): void
    {
        $this->blockPath = rtrim($path, '/');
    }

    /**
     * Boot block and initialize hooks.
     * Called before register().
     */
    public function boot(): void
    {
        // Override in child classes to register actions/filters
    }

    /**
     * Register editor assets (admin only).
     */
    protected function registerEditorAssets(): void
    {
        // Override in child classes
    }

    /**
     * Register frontend assets (frontend only).
     */
    protected function registerFrontendAssets(): void
    {
        // Override in child classes
    }

    /**
     * Register the block with WordPress.
     * Uses register_block_type_from_metadata() to read block.json directly.
     * Optimized: registers once, assets only enqueued when needed.
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        if (!$this->blockId || !$this->blockPath) {
            return;
        }

        $blockJson = $this->blockPath . '/block.json';
        if (!file_exists($blockJson) || !is_readable($blockJson)) {
            return;
        }

        $args = [];

        // Add render_callback if child class implements render()
        if (method_exists($this, 'render') && ($this::class !== AbstractBlock::class)) {
            $args['render_callback'] = [$this, 'render'];
        }

        $args = apply_filters('timeless_block_args', $args, $this->blockId, $this);

        $registered = register_block_type_from_metadata($this->blockPath, $args);

        if ($registered instanceof \WP_Block_Type) {
            $this->registerAssets($registered);
        }

        $this->registered = true;
    }

    /**
     * Register block assets (style, viewScript).
     * Called during register() - assets are registered but only enqueued
     * when block is actually rendered on the page.
     */
    protected function registerAssets(\WP_Block_Type $block): void
    {
        $version = wp_get_theme()->get('Version');
        $handlePrefix = str_replace('/', '-', $this->blockId);

        // Register block style
        if ($block->style) {
            $handle = $handlePrefix . '-style';
            if (!wp_style_is($handle, 'registered')) {
                // Try to resolve URL from block metadata
                $url = $this->resolveAssetUrl('style');
                if ($url) {
                    wp_register_style($handle, $url, [], $version);
                }
            }
            wp_enqueue_style($handle);
        }

        // Register view script (frontend JS)
        if ($block->view_script) {
            $handle = $handlePrefix . '-view';
            if (!wp_script_is($handle, 'registered')) {
                $url = $this->resolveAssetUrl('viewScript');
                if ($url) {
                    wp_register_script($handle, $url, [], $version, true);
                }
            }
            wp_enqueue_script($handle);
        }
    }

    /**
     * Resolve asset URL from block metadata "file:..." references.
     */
    protected function resolveAssetUrl(string $type): ?string
    {
        $blockJson = $this->blockPath . '/block.json';
        if (!file_exists($blockJson)) {
            return null;
        }

        $json = file_get_contents($blockJson);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $fieldMap = [
            'style'    => 'style',
            'viewScript' => 'viewScript',
        ];

        $key = $fieldMap[$type] ?? $type;
        $ref = $data[$key] ?? null;

        if (!is_string($ref) || str_starts_with($ref, 'file:') === false) {
            return null;
        }

        $relativePath = ltrim(substr($ref, 5), '/');
        $absolutePath = $this->blockPath . '/' . $relativePath;

        if (!file_exists($absolutePath)) {
            return null;
        }

        return str_replace(
            get_template_directory(),
            get_template_directory_uri(),
            $absolutePath
        );
    }

    /**
     * WordPress render_callback.
     * Ensures frontend assets are enqueued then delegates to render().
     */
    public function render(array $attributes = [], string $content = '', \WP_Block $block = null): string
    {
        // Ensure frontend assets are enqueued at render time
        if (!$this->registered) {
            $this->register();
        }

        return $this->renderBlock($attributes, $content, $block);
    }

    /**
     * Render the block on the frontend.
     * Override in child classes for SSR output.
     *
     * @param array  $attributes Block attributes
     * @param string $content    Inner block content
     * @param \WP_Block|null $block Block instance
     * @return string HTML output
     */
    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        return $content;
    }

    /**
     * Get block CSS class name.
     * Converts jankx/advanced-button -> wp-block-jankx-advanced-button
     */
    public function getBlockClass(string $extra = ''): string
    {
        $class = 'wp-block-' . str_replace('/', '-', $this->blockId);
        if ($extra !== '') {
            $class .= ' ' . $extra;
        }
        return $class;
    }

    /**
     * Get frontend asset handles for this block.
     * Returns handles registered via register_block_type_from_metadata.
     *
     * @return string[] Asset handles
     */
    public function getFrontendAssets(): array
    {
        $assets = [];
        $handlePrefix = str_replace('/', '-', $this->blockId);

        $blockJson = $this->blockPath . '/block.json';
        if (!file_exists($blockJson)) {
            return $assets;
        }

        $json = file_get_contents($blockJson);
        if ($json === false) {
            return $assets;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return $assets;
        }

        if (isset($data['style'])) {
            $assets[] = $handlePrefix . '-style';
        }
        if (isset($data['viewScript'])) {
            $assets[] = $handlePrefix . '-view';
        }

        return $assets;
    }

    /**
     * Check if block should render on frontend.
     *
     * @param array $attributes Block attributes
     * @return bool True to render, false to skip output
     */
    public function shouldRender(array $attributes): bool
    {
        return true;
    }

    /**
     * Escape and output attributes as HTML data attributes.
     */
    protected function dataAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $html .= sprintf(' data-%s="%s"', esc_attr($key), esc_attr(wp_json_encode($value)));
            }
        }
        return $html;
    }
}
