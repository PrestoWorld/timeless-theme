<?php

namespace Timeless\Blocks;

interface BlockRendererInterface
{
    public function render(array $attributes = [], string $content = '', \WP_Block $block = null): string;

    public function getFrontendAssets(): array;

    public function shouldRender(array $attributes): bool;
}
