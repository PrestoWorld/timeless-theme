<?php

namespace Timeless\Blocks;

class FilterRadioBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/filter-radio';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        return '';
    }

    protected function shouldRender(array $attributes): bool
    {
        return false;
    }
}
