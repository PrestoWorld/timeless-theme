<?php

namespace Timeless\Blocks;

class CarouselArrowsBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/carousel-arrows';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        // Settings-only block used inside dynamic-data-layout and dynamic-term-layout.
        // Does not render any markup on the frontend. Parent blocks read its
        // attributes server-side to render styled carousel prev/next buttons.
        return '';
    }

    protected function shouldRender(array $attributes): bool
    {
        return false;
    }
}
