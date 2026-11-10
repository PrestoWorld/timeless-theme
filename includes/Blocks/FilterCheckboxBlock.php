<?php

namespace Timeless\Blocks;

class FilterCheckboxBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/filter-checkbox';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        return '';
    }

    protected function shouldRender(array $attributes): bool
    {
        return false;
    }
}
