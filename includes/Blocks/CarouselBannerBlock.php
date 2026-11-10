<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function get_post_meta;
use function get_the_post_thumbnail_url;
use function is_array;
use function number_format;
use function sanitize_html_class;
use function sprintf;
use function strpos;
use function wp_get_attachment_image_url;

class CarouselBannerBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/carousel-banner';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $imageId        = (int) ($attributes['imageId'] ?? 0);
        $imageUrl       = $attributes['imageUrl'] ?? '';
        $imageAlt       = $attributes['imageAlt'] ?? '';
        $linkUrl        = $attributes['linkUrl'] ?? '';
        $linkTarget     = $attributes['linkTarget'] ?? '_self';
        $bannerStyle    = $attributes['bannerStyle'] ?? 'banner';
        $overlayOpacity = (float) ($attributes['overlayOpacity'] ?? 0.3);
        $overlayColor   = $attributes['overlayColor'] ?? '#000000';
        $textAlign      = $attributes['textAlign'] ?? 'center';
        $textPosition   = $attributes['textPosition'] ?? 'middle';
        $height         = (int) ($attributes['height'] ?? 0);
        $imageSize      = $attributes['imageSize'] ?? 'cover';
        $className      = $attributes['className'] ?? '';

        if ($imageId > 0 && empty($imageUrl)) {
            $imageUrl = wp_get_attachment_image_url($imageId, 'full') ?: '';
            if (empty($imageAlt)) {
                $imageAlt = get_post_meta($imageId, '_wp_attachment_image_alt', true) ?: '';
            }
        }

        if (!empty($content) && strpos($content, 'carousel-banner__overlay-content') !== false) {
            if (!empty($imageUrl)) {
                $content = preg_replace(
                    '/background-image:url\([^)]*\)/',
                    'background-image:url(\'' . esc_url($imageUrl) . '\')',
                    $content
                );
                if (!empty($imageAlt)) {
                    $content = preg_replace(
                        '/aria-label="[^"]*"/',
                        'aria-label="' . esc_attr($imageAlt) . '"',
                        $content
                    );
                }
            }
            return $content;
        }

        $wrapperClasses = array_filter([
            'embla__slide',
            'wp-block-jankx-carousel-banner',
            'carousel-banner',
            'carousel-banner--' . sanitize_html_class($bannerStyle),
            'text-' . sanitize_html_class($textAlign),
            'text-position-' . sanitize_html_class($textPosition),
            'image-size-' . sanitize_html_class($imageSize),
            $className,
        ]);

        $wrapperStyle = '';
        if ($height > 0) {
            $wrapperStyle = sprintf('--banner-height:%dpx;', $height);
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => implode(' ', $wrapperClasses),
            'style' => $wrapperStyle,
        ]);

        $bgStyles = [];
        if (!empty($imageUrl)) {
            $bgStyles[] = 'background-image:url(\'' . esc_url($imageUrl) . '\')';
        }
        switch ($imageSize) {
            case 'fullwidth':
                $bgStyles[] = 'background-size:100% 100%';
                $bgStyles[] = 'background-position:center';
                break;
            case 'contain':
                $bgStyles[] = 'background-size:contain';
                break;
            default:
                $bgStyles[] = 'background-size:cover';
                break;
        }

        $overlayStyle = sprintf(
            'background-color:%s;opacity:%s;',
            esc_attr($overlayColor),
            number_format($overlayOpacity, 2, '.', '')
        );

        ob_start();
        ?>
        <div <?php echo $wrapperAttrs; ?>>
            <?php if (!empty($linkUrl)) : ?>
                <a href="<?php echo esc_url($linkUrl); ?>"
                   target="<?php echo esc_attr($linkTarget); ?>"
                   class="carousel-banner__link"
                   rel="<?php echo $linkTarget === '_blank' ? 'noopener noreferrer' : ''; ?>"
                   aria-label="<?php echo esc_attr($imageAlt); ?>">
            <?php endif; ?>

            <?php if (!empty($imageUrl)) : ?>
                <div class="carousel-banner__image image-size-<?php echo esc_attr($imageSize); ?>"
                     style="<?php echo esc_attr(implode(';', $bgStyles)); ?>"
                     role="img"
                     <?php if (!empty($imageAlt)) : ?>aria-label="<?php echo esc_attr($imageAlt); ?>"<?php endif; ?>
                ></div>
            <?php endif; ?>

            <?php if (!empty($imageUrl) && $overlayOpacity > 0) : ?>
                <div class="carousel-banner__overlay"
                     style="<?php echo esc_attr($overlayStyle); ?>"
                     aria-hidden="true"></div>
            <?php endif; ?>

            <div class="carousel-banner__overlay-content">
                <?php echo $content; ?>
            </div>

            <?php if (!empty($linkUrl)) : ?>
                </a>
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
