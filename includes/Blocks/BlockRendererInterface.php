<?php
/**
 * Block Renderer Interface
 *
 * Contract for block frontend renderers. Implementations provide the
 * server-side render_callback used by register_block_type().
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

/**
 * Interface BlockRendererInterface
 */
interface BlockRendererInterface
{
    /**
     * Render the block on the frontend.
     *
     * @param array  $attributes Block attributes from block.json
     * @param string $content    Inner block content (for InnerBlocks)
     * @return string HTML output
     */
    public function render(array $attributes, string $content): string;

    /**
     * Get block assets to enqueue on frontend (viewScript, style).
     * Returns array of asset handles already registered/enqueued.
     *
     * @return string[] Asset handles
     */
    public function getFrontendAssets(): array;

    /**
     * Check if block should render on frontend or return null (skip).
     *
     * @param array $attributes Block attributes
     * @return bool True to render, false to skip output
     */
    public function shouldRender(array $attributes): bool;
}
