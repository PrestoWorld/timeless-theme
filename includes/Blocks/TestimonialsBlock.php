<?php

namespace Timeless\Blocks;

use function absint;
use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function intval;
use function preg_match;
use function preg_replace;
use function sprintf;

class TestimonialsBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/testimonials';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $layout = $attributes['layout'] ?? 'default';
        $className = $attributes['className'] ?? '';
        $anchor = $attributes['anchor'] ?? '';
        $slides = absint($attributes['slidesPerView'] ?? 1);
        $spaceBetween = absint($attributes['spaceBetween'] ?? 30);
        $loop = !empty($attributes['loop']);
        $autoplay = !empty($attributes['autoplay']);
        $autoplayDelay = absint($attributes['autoplayDelay'] ?? 3000);
        $navigation = !empty($attributes['navigation']);
        $pagination = !empty($attributes['pagination']);
        $height = absint($attributes['height'] ?? 50);
        $minHeight = absint($attributes['minHeight'] ?? 50);
        $wrapperAttributes = [];
        if (!empty($anchor)) {
            $wrapperAttributes['id'] = esc_attr($anchor);
        }
        $wrapper = get_block_wrapper_attributes($wrapperAttributes);
        $classes = 'jankx-testimonials-container layout-' . esc_attr($layout);
        if (!empty($className)) {
            $classes = trim($classes . ' ' . esc_attr($className));
        }
        if (preg_match('/class=["\']([^"\']*)["\']/', $wrapper, $matches)) {
            $existing = trim($matches[1]);
            $all = trim($existing . ' ' . $classes);
            $wrapper = preg_replace('/class=["\'][^"\']*["\']/', 'class="' . esc_attr($all) . '"', $wrapper);
        } else {
            $wrapper .= ' class="' . esc_attr($classes) . '"';
        }
        if ($layout === 'carousel' || $layout === 'banner') {
            $style = sprintf('--swiper-height:%dpx; --swiper-min-height:%dpx;', $height, $minHeight);
            $containerAttrs = sprintf(
                'data-slides-per-view="%s" data-space-between="%s" data-loop="%s" data-autoplay="%s" data-autoplay-delay="%s" data-navigation="%s" data-pagination="%s" data-swiper-height="%s"',
                absint($slides),
                absint($spaceBetween),
                $loop ? 'true' : 'false',
                $autoplay ? 'true' : 'false',
                esc_attr($autoplayDelay),
                $navigation ? 'true' : 'false',
                $pagination ? 'true' : 'false',
                absint($height)
            );
            return sprintf(
                '<div %s style="%s"><div class="swiper" %s><div class="swiper-wrapper">%s</div>%s%s</div></div>',
                $wrapper,
                esc_attr($style),
                $containerAttrs,
                $content,
                $navigation ? '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>' : '',
                $pagination ? '<div class="swiper-pagination"></div>' : ''
            );
        }
        return sprintf('<div %s>%s</div>', $wrapper, $content);
    }
}
