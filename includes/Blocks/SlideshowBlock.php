<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function get_block_wrapper_attributes;
use function in_array;
use function preg_replace;
use function sprintf;
use function trim;
use function uniqid;
use function wp_kses;
use function wp_kses_allowed_html;

class SlideshowBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/slideshow';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $autoplay            = $attributes['autoplay'] ?? false;
        $autoplayDelay       = (int) ($attributes['autoplayDelay'] ?? 3000);
        $fullscreen          = $attributes['fullscreen'] ?? true;
        $fullscreenText      = $attributes['fullscreenText'] ?? __('Fullscreen', 'timeless');
        $prevText            = $attributes['prevText'] ?? '<';
        $nextText            = $attributes['nextText'] ?? '>';
        $showThumbnails      = $attributes['showThumbnails'] ?? true;
        $showNavigation      = $attributes['showNavigation'] ?? true;
        $showPagination      = $attributes['showPagination'] ?? true;
        $transitionEffect    = $attributes['transitionEffect'] ?? 'slide';
        $transitionSpeed     = (int) ($attributes['transitionSpeed'] ?? 300);
        $thumbnailSize       = $attributes['thumbnailSize'] ?? 'medium';
        $mainImageHeight     = (int) ($attributes['mainImageHeight'] ?? 400);
        $captionPosition     = $attributes['captionPosition'] ?? 'hidden';
        $enableLightbox      = $attributes['enableLightbox'] ?? false;
        $className           = $attributes['className'] ?? '';
        $anchor              = $attributes['anchor'] ?? '';

        switch ($thumbnailSize) {
            case 'small':
                $thumbnailSizeCss = '60px';
                break;
            case 'large':
                $thumbnailSizeCss = '100px';
                break;
            default:
                $thumbnailSizeCss = '80px';
        }

        $wrapperClasses = array_filter([
            'slideshow-block',
            $enableLightbox ? 'photoswipe-enabled' : '',
            $className,
        ]);

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => trim(implode(' ', $wrapperClasses)),
            'id' => $anchor !== '' ? esc_attr($anchor) : null,
            'style' => sprintf(
                '--slideshow-height: %dpx; --slideshow-transition-speed: %dms; --slideshow-thumbnail-size: %s;',
                $mainImageHeight,
                $transitionSpeed,
                $thumbnailSizeCss
            ),
            'data-slideshow' => 'true',
        ]);

        $dataAttrs = sprintf(
            'data-autoplay="%s" data-autoplay-delay="%s" data-fullscreen="%s" data-show-thumbnails="%s" data-show-navigation="%s" data-show-pagination="%s" data-transition-effect="%s" data-transition-speed="%s" data-thumbnail-size="%s" data-enable-lightbox="%s"',
            $autoplay ? 'true' : 'false',
            esc_attr($autoplayDelay),
            $fullscreen ? 'true' : 'false',
            $showThumbnails ? 'true' : 'false',
            $showNavigation ? 'true' : 'false',
            $showPagination ? 'true' : 'false',
            esc_attr($transitionEffect),
            esc_attr($transitionSpeed),
            esc_attr($thumbnailSize),
            $enableLightbox ? 'true' : 'false'
        );

        ob_start();
        ?>
        <div <?php echo $wrapperAttributes; ?> <?php echo $dataAttrs; ?>>
            <?php
            if ($block && !empty($block->inner_blocks)) {
                foreach ($block->inner_blocks as $innerBlock) {
                    if ($innerBlock->name === 'jankx/slideshow-container') {
                        $innerBlock->context = array_merge($innerBlock->context ?? [], [
                            'jankx/showThumbnails' => $showThumbnails,
                            'jankx/showNavigation' => $showNavigation,
                            'jankx/transitionEffect' => $transitionEffect,
                            'jankx/captionPosition' => $captionPosition,
                            'jankx/prevText' => $prevText,
                            'jankx/nextText' => $nextText,
                        ]);
                        echo $innerBlock->render();
                        break;
                    }
                }

                $footerContent = '';
                foreach ($block->inner_blocks as $innerBlock) {
                    if (in_array($innerBlock->name, ['core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/group'], true)) {
                        $footerContent .= $innerBlock->render();
                    }
                }
            } else {
                $footerContent = $content;
            }
            ?>

            <?php if ($block && !empty($block->inner_blocks)) : ?>
                <?php
                $hasSlideshowContainer = false;
                foreach ($block->inner_blocks as $innerBlock) {
                    if ($innerBlock->name === 'jankx/slideshow-container') {
                        $hasSlideshowContainer = true;
                        break;
                    }
                }

                if ($hasSlideshowContainer) : ?>
                    <div class="slideshow-footer">
                        <?php if ($footerContent !== '') : ?>
                            <div class="slideshow-footer-text">
                                <?php echo $footerContent; ?>
                            </div>
                        <?php endif; ?>

                        <div class="slideshow-footer-bottom">
                            <div class="slideshow-controls">
                                <?php if ($fullscreen) : ?>
                                    <button class="slideshow-fullscreen-btn"><?php echo esc_html($fullscreenText); ?></button>
                                <?php endif; ?>

                                <?php if ($autoplay) : ?>
                                    <button class="slideshow-autoplay-btn"><?php esc_html_e('Autoplay', 'timeless'); ?></button>
                                <?php endif; ?>
                            </div>

                            <?php if ($showPagination) : ?>
                                <div class="slideshow-pagination">
                                    <button class="slideshow-pagination-prev" disabled><?php echo $this->allowSvgTags($prevText); ?></button>
                                    <?php if ($block && !empty($block->inner_blocks)) : ?>
                                        <?php foreach ($block->inner_blocks as $innerBlock) : ?>
                                            <?php if ($innerBlock->name === 'jankx/slideshow-container') : ?>
                                                <?php
                                                $images = $innerBlock->attributes['images'] ?? [];
                                                if (!empty($images)) :
                                                    foreach ($images as $index => $image) :
                                                        $activeClass = $index === 0 ? 'active' : '';
                                                        ?>
                                                        <button class="slideshow-pagination-dot <?php echo esc_attr($activeClass); ?>" data-slide="<?php echo esc_attr($index); ?>">
                                                            <?php echo esc_html($index + 1); ?>
                                                        </button>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <button class="slideshow-pagination-next"><?php echo $this->allowSvgTags($nextText); ?></button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
