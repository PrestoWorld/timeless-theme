<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function in_array;
use function preg_replace;
use function sprintf;
use function wp_kses;
use function wp_kses_allowed_html;

class SlideshowContainerBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/slideshow-container';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $containerId = $attributes['containerId'] ?? '';
        $images = $attributes['images'] ?? [];

        $showThumbnails = $block ? ($block->context['jankx/showThumbnails'] ?? true) : true;
        $showNavigation = $block ? ($block->context['jankx/showNavigation'] ?? true) : true;
        $prevText = $block ? ($block->context['jankx/prevText'] ?? '<') : '<';
        $nextText = $block ? ($block->context['jankx/nextText'] ?? '>') : '>';

        ob_start();
        ?>
        <?php if ($showThumbnails && !empty($images) && count($images) > 1) : ?>
            <div class="slideshow-thumbnails">
                <?php foreach ($images as $index => $image) : ?>
                    <?php
                    $thumbnailUrl = $image['thumbnailUrl'] ?? $image['url'] ?? '';
                    $altText = $image['alt'] ?? '';
                    $activeClass = $index === 0 ? 'active' : '';
                    ?>
                    <button class="slideshow-thumbnail <?php echo esc_attr($activeClass); ?>" data-slide="<?php echo esc_attr($index); ?>">
                        <?php if ($thumbnailUrl !== '') : ?>
                            <img src="<?php echo esc_url($thumbnailUrl); ?>" alt="<?php echo esc_attr($altText); ?>" />
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="slideshow-main">
            <div class="slideshow-container">
                <div class="slideshow-track">
                    <?php echo $content; ?>
                </div>
            </div>

            <?php if ($showNavigation && !empty($images) && count($images) > 1) : ?>
                <button class="slideshow-nav slideshow-nav-prev" aria-label="<?php esc_attr_e('Previous slide', 'timeless'); ?>">
                    <?php echo $this->allowSvgTags($prevText); ?>
                </button>
                <button class="slideshow-nav slideshow-nav-next" aria-label="<?php esc_attr_e('Next slide', 'timeless'); ?>">
                    <?php echo $this->allowSvgTags($nextText); ?>
                </button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function allowSvgTags(string $content): string
    {
        $allowedTags = wp_kses_allowed_html('post');
        $allowedTags['svg'] = [
            'width' => true,
            'height' => true,
            'viewBox' => true,
            'fill' => true,
            'xmlns' => true,
            'class' => true,
            'style' => true,
        ];
        $allowedTags['path'] = [
            'd' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
        ];
        $allowedTags['circle'] = [
            'cx' => true,
            'cy' => true,
            'r' => true,
            'fill' => true,
            'stroke' => true,
        ];
        $allowedTags['rect'] = [
            'x' => true,
            'y' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
            'stroke' => true,
        ];
        $allowedTags['line'] = [
            'x1' => true,
            'y1' => true,
            'x2' => true,
            'y2' => true,
            'stroke' => true,
            'stroke-width' => true,
        ];

        $sanitized = wp_kses($content, $allowedTags);
        $sanitized = preg_replace('/fill="[^"]*"/', 'fill="currentColor"', $sanitized);

        return $sanitized;
    }
}
