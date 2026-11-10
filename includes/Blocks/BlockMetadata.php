<?php
/**
 * Block Metadata Value Object
 *
 * Immutable data carrier for Gutenberg block metadata parsed from block.json.
 * Resolves asset paths relative to the block directory.
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

/**
 * Class BlockMetadata
 */
class BlockMetadata
{
    /**
     * Block name (e.g. jankx/advanced-button)
     */
    private string $name;

    /**
     * Absolute path to block directory
     */
    private string $blockPath;

    /**
     * Parsed block.json data
     */
    private array $data;

    /**
     * Cached resolved asset URLs
     */
    private array $assetUrls = [];

    /**
     * BlockMetadata constructor.
     */
    public function __construct(string $name, string $blockPath, array $data)
    {
        $this->name = $name;
        $this->blockPath = rtrim($blockPath, '/');
        $this->data = $data;
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
     * Get raw block.json data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get a specific attribute from block.json
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get block attributes schema
     */
    public function getAttributes(): array
    {
        return $this->data['attributes'] ?? [];
    }

    /**
     * Get block supports
     */
    public function getSupports(): array
    {
        return $this->data['supports'] ?? [];
    }

    /**
     * Get block styles
     */
    public function getStyles(): array
    {
        return $this->data['styles'] ?? [];
    }

    /**
     * Get block category
     */
    public function getCategory(): string
    {
        return $this->data['category'] ?? 'common';
    }

    /**
     * Get block title
     */
    public function getTitle(): string
    {
        return $this->data['title'] ?? $this->name;
    }

    /**
     * Get block description
     */
    public function getDescription(): string
    {
        return $this->data['description'] ?? '';
    }

    /**
     * Get textdomain
     */
    public function getTextdomain(): string
    {
        return $this->data['textdomain'] ?? 'timeless';
    }

    /**
     * Resolve and cache asset URL for a given asset type.
     *
     * @param string $type One of: editorScript, editorStyle, style, viewScript
     * @return string|null Asset URL or null if not defined
     */
    public function getAssetUrl(string $type): ?string
    {
        if (isset($this->assetUrls[$type])) {
            return $this->assetUrls[$type];
        }

        $raw = $this->data[$type] ?? null;
        if (is_null($raw) || !is_string($raw)) {
            $this->assetUrls[$type] = null;
            return null;
        }

        // Handle "file:..." prefix used by @wordpress/scripts
        if (str_starts_with($raw, 'file:')) {
            $relativePath = substr($raw, 5);
            $absolutePath = $this->blockPath . '/' . ltrim($relativePath, '/');
            $url = str_replace(
                get_template_directory(),
                get_template_directory_uri(),
                $absolutePath
            );
        } else {
            $url = $raw;
        }

        $this->assetUrls[$type] = $url;
        return $url;
    }

    /**
     * Check if an asset type is defined
     */
    public function hasAsset(string $type): bool
    {
        return isset($this->data[$type]) && is_string($this->data[$type]);
    }
}
