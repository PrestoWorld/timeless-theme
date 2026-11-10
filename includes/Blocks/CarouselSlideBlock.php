<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function is_array;
use function number_format;
use function sprintf;
use function strpos;
use function wp_kses_post;

class CarouselSlideBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/carousel-slide';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $imageSize      = $attributes['imageSize'] ?? 'cover';
        $className      = $attributes['className'] ?? '';
        $overlayColor   = $attributes['overlayColor'] ?? '';
        $overlayOpacity = (int) ($attributes['overlayOpacity'] ?? 40);

        if (!empty($content) && strpos($content, 'carousel-slide__content') !== false) {
            if (!empty($overlayColor)) {
                $opacity = (float) $overlayOpacity / 100;
                $overlayStyle = sprintf(
                    'background-color:%s;opacity:%s;position:absolute;inset:0;pointer-events:none;z-index:1;',
                    esc_attr($overlayColor),
                    $opacity
                );
                if (strpos($content, 'carousel-slide__overlay') !== false) {
                    $content = preg_replace(
                        '/<div class="carousel-slide__overlay" style="[^"]*"><\/div>/',
                        '<div class="carousel-slide__overlay" style="' . esc_attr($overlayStyle) . '"></div>',
                        $content
                    );
                } else {
                    $content = preg_replace(
                        '/(<div class="carousel-slide__content"[^>]*>)/',
                        '$1<div class="carousel-slide__overlay" style="' . esc_attr($overlayStyle) . '"></div>',
                        $content
                    );
                }
            }
            return $content;
        }

        $slideId = $attributes['slideId'] ?? '';

        $wrapperClasses = ['embla__slide'];
        if ($imageSize !== '') {
            $wrapperClasses[] = 'image-size-' . esc_attr($imageSize);
        }
        if ($className !== '') {
            $wrapperClasses[] = esc_attr($className);
        }

        $wrapperAttributes = ['class' => implode(' ', $wrapperClasses)];

        if ($slideId !== '') {
            $wrapperAttributes['data-slide-id'] = esc_attr($slideId);
        }
        if ($imageSize !== '') {
            $wrapperAttributes['data-image-size'] = esc_attr($imageSize);
        }

        $blockWrapperAttrs = get_block_wrapper_attributes($wrapperAttributes);

        $overlayStyle = '';
        if (!empty($overlayColor)) {
            $opacity = (float) $overlayOpacity / 100;
            $overlayStyle = sprintf(
                'background-color: %s; opacity: %s; position: absolute; inset: 0; pointer-events: none; z-index: 1;',
                esc_attr($overlayColor),
                $opacity
            );
        }

        ob_start();
        ?>
        <div <?php echo $blockWrapperAttrs; ?>>
            <?php if (!empty($overlayColor)) : ?>
                <div class="carousel-slide__overlay" style="<?php echo $overlayStyle; ?>"></div>
            <?php endif; ?>
            <div class="carousel-slide__content" style="position: relative; z-index: 2; width: 100%; height: 100%;">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
