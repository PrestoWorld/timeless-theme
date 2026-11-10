<?php

namespace Timeless\Blocks;

use function esc_attr;
use function is_array;
use function sprintf;
use function strpos;
use function wp_kses_post;

class CarouselInnerBlocksOverlayBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/carousel-inner-blocks-overlay';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if (!empty($content) && strpos($content, 'carousel-inner-blocks-overlay') !== false) {
            return $content;
        }

        $className = $attributes['className'] ?? '';

        $wrapperClasses = ['carousel-inner-blocks-overlay'];
        if ($className !== '') {
            $wrapperClasses[] = esc_attr($className);
        }

        $wrapperAttributes = [
            'class' => implode(' ', $wrapperClasses),
            'style' => 'z-index:1;-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none',
            'data-overlay' => 'true',
        ];

        $blockWrapperAttrs = get_block_wrapper_attributes($wrapperAttributes);

        ob_start();
        ?>
        <div <?php echo $blockWrapperAttrs; ?>>
            <?php echo wp_kses_post($content); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
