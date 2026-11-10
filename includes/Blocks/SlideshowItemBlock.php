<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_url;
use function get_post_meta;
use function wp_get_attachment_image_url;
use function wp_kses_post;

class SlideshowItemBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/slideshow-item';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $imageId = (int) ($attributes['imageId'] ?? 0);
        $imageUrl = $attributes['imageUrl'] ?? '';
        $imageAlt = $attributes['imageAlt'] ?? '';
        $imageCaption = $attributes['imageCaption'] ?? '';
        $slideId = $attributes['slideId'] ?? '';

        $captionPosition = 'hidden';

        if ($imageId > 0 && empty($imageUrl)) {
            $imageUrl = wp_get_attachment_image_url($imageId, 'full') ?: '';
            if (empty($imageAlt)) {
                $imageAlt = get_post_meta($imageId, '_wp_attachment_image_alt', true) ?: '';
            }
        }

        ob_start();
        ?>
        <div class="slideshow-slide slideshow-caption-<?php echo esc_attr($captionPosition); ?>" data-slide-id="<?php echo esc_attr($slideId); ?>">
            <?php if ($imageUrl !== '') : ?>
                <img src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr($imageAlt); ?>" />
            <?php endif; ?>

            <?php if (!empty($imageCaption) && $captionPosition !== 'hidden') : ?>
                <div class="slideshow-caption"><?php echo wp_kses_post($imageCaption); ?></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
