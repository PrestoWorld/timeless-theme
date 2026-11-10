<?php
/**
 * Abstract Block Base Class
 *
 * Base class for all Gutenberg block implementations. Provides
 * registration logic, asset resolution, and a render contract.
 * Uses lazy initialization to minimize memory footprint.
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

use function apply_filters;
use function file_exists;
use function is_readable;
use function json_decode;
use function json_encode;
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
     * Block metadata (lazy loaded)
     */
    private ?BlockMetadata $metadata = null;

    /**
     * Block name (e.g. jankx/advanced-button)
     */
    private string $name;

    /**
     * Absolute path to block directory
     */
    private string $blockPath;

    /**
     * Whether the block has been registered with WordPress
     */
    private bool $registered = false;

    /**
     * AbstractBlock constructor.
     */
    public function __construct(string $name, string $blockPath)
    {
        $this->name = $name;
        $this->blockPath = rtrim($blockPath, '/');
    }

    /**
     * Get block name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get block directory path
     */
    public function getBlockPath(): string
    {
        return $this->blockPath;
    }

    /**
     * Get block metadata (lazy loaded from block.json)
     */
    public function getMetadata(): BlockMetadata
    {
        if ($this->metadata === null) {
            $this->metadata = $this->loadMetadata();
        }
        return $this->metadata;
    }

    /**
     * Load and parse block.json
     */
    protected function loadMetadata(): BlockMetadata
    {
        $blockJson = $this->blockPath . '/block.json';

        if (!file_exists($blockJson) || !is_readable($blockJson)) {
            // Fallback: minimal metadata from class name
            return new BlockMetadata(
                $this->name,
                $this->blockPath,
                [
                    'name'        => $this->name,
                    'title'       => $this->name,
                    'category'    => 'common',
                    'textdomain'  => 'timeless',
                    'attributes'  => [],
                    'supports'    => [],
                ]
            );
        }

        $json = file_get_contents($blockJson);
        if ($json === false) {
            throw new \RuntimeException(sprintf(
                'Failed to read block.json for block "%s"',
                $this->name
            ));
        }

        $data = json_decode($json, true);
        if ($data === null) {
            throw new \RuntimeException(sprintf(
                'Invalid JSON in block.json for block "%s"',
                $this->name
            ));
        }

        return new BlockMetadata($this->name, $this->blockPath, $data);
    }

    /**
     * Register the block with WordPress.
     * Optimized: only registers once, reuses metadata.
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $metadata = $this->getMetadata();
        $name = $this->name;

        $blockArgs = [
            'attributes'   => $metadata->getAttributes(),
            'supports'     => $metadata->getSupports(),
            'styles'       => $metadata->getStyles(),
            'category'     => $metadata->getCategory(),
            'title'        => $metadata->getTitle(),
            'description'  => $metadata->getDescription(),
            'textdomain'   => $metadata->getTextdomain(),
            'render_callback' => [$this, 'renderBlock'],
        ];

        // Allow filtering block args before registration
        $blockArgs = apply_filters('timeless_block_args', $blockArgs, $name, $this);

        register_block_type($name, $blockArgs);

        // Register frontend assets if defined
        $this->registerFrontendAssets($metadata);

        $this->registered = true;
    }

    /**
     * Register frontend assets (style, viewScript) lazily.
     * Assets are registered but only enqueued when block is rendered.
     */
    protected function registerFrontendAssets(BlockMetadata $metadata): void
    {
        $handlePrefix = str_replace('/', '-', $this->name);

        // Register block style
        if ($metadata->hasAsset('style')) {
            $url = $metadata->getAssetUrl('style');
            if ($url) {
                $handle = $handlePrefix . '-style';
                wp_register_style($handle, $url, [], wp_get_theme()->get('Version'));
                wp_enqueue_style($handle);
            }
        }

        // Register view script (frontend JS)
        if ($metadata->hasAsset('viewScript')) {
            $url = $metadata->getAssetUrl('viewScript');
            if ($url) {
                $handle = $handlePrefix . '-view';
                wp_register_script($handle, $url, [], wp_get_theme()->get('Version'), true);
                wp_enqueue_script($handle);
            }
        }
    }

    /**
     * WordPress render_callback wrapper.
     * Ensures assets are enqueued then delegates to render().
     */
    public function renderBlock(array $attributes, string $content = ''): string
    {
        if (!$this->shouldRender($attributes)) {
            return '';
        }

        // Ensure frontend assets are enqueued at render time
        $this->registerFrontendAssets($this->getMetadata());

        return $this->render($attributes, $content);
    }

    /**
     * Get frontend asset handles for this block.
     * Used by BlockRegistry to conditionally enqueue assets.
     */
    public function getFrontendAssets(): array
    {
        $assets = [];
        $metadata = $this->getMetadata();
        $handlePrefix = str_replace('/', '-', $this->name);

        if ($metadata->hasAsset('style')) {
            $assets[] = $handlePrefix . '-style';
        }
        if ($metadata->hasAsset('viewScript')) {
            $assets[] = $handlePrefix . '-view';
        }

        return $assets;
    }

    /**
     * Default: block should always render.
     */
    public function shouldRender(array $attributes): bool
    {
        return true;
    }

    /**
     * Get block attributes with defaults applied.
     */
    public function getAttributesWithDefaults(array $attributes = []): array
    {
        $schema = $this->getMetadata()->getAttributes();
        $merged = [];

        foreach ($schema as $attrName => $attrDef) {
            $default = $attrDef['default'] ?? null;
            $merged[$attrName] = $attributes[$attrName] ?? $default;
        }

        return array_merge($merged, $attributes);
    }

    /**
     * Escape and output attributes as HTML data attributes.
     * Uses wp_json_encode for safe attribute serialization.
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

    /**
     * Get block CSS class name.
     * Converts jankx/advanced-button -> wp-block-jankx-advanced-button
     */
    public function getBlockClass(string $extra = ''): string
    {
        $class = 'wp-block-' . str_replace('/', '-', $this->name);
        if ($extra !== '') {
            $class .= ' ' . $extra;
        }
        return $class;
    }
}
