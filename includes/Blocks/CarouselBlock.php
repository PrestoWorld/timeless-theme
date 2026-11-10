<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function is_object;
use function number_format;
use function preg_match;
use function sprintf;
use function substr_count;

class CarouselBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/carousel';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $slidesPerView        = (int) ($attributes['slidesPerView'] ?? 1);
        $slidesPerViewTablet  = (int) ($attributes['slidesPerViewTablet'] ?? $slidesPerView);
        $slidesPerViewMobile  = (int) ($attributes['slidesPerViewMobile'] ?? $slidesPerView);
        $spaceBetween         = (int) ($attributes['spaceBetween'] ?? 30);
        $loop                 = $attributes['loop'] ?? true;
        $autoplay             = $attributes['autoplay'] ?? false;
        $autoplayDelay        = (int) ($attributes['autoplayDelay'] ?? 3000);
        $speed                = (int) ($attributes['speed'] ?? 300);
        $navigation           = $attributes['navigation'] ?? true;
        $pagination           = $attributes['pagination'] ?? true;
        $height               = (int) ($attributes['height'] ?? 400);
        $minHeight            = (int) ($attributes['minHeight'] ?? 50);
        $className            = $attributes['className'] ?? '';
        $anchor               = $attributes['anchor'] ?? '';
        $fullHeight           = $attributes['fullHeight'] ?? false;
        $fitViewportMinusHeader = $attributes['fitViewportMinusHeader'] ?? false;

        $bannerStyle          = $attributes['bannerStyle'] ?? 'default';
        $bannerTextColor      = $attributes['bannerTextColor'] ?? '#ffffff';
        $bannerBackgroundColor= $attributes['bannerBackgroundColor'] ?? 'rgba(0,0,0,0.5)';
        $bannerPadding        = (int) ($attributes['bannerPadding'] ?? 20);
        $bannerBorderRadius   = (int) ($attributes['bannerBorderRadius'] ?? 0);

        $gradientOverlay      = $attributes['gradientOverlay'] ?? false;
        $gradientColor        = $attributes['gradientColor'] ?? '#000000';
        $gradientOpacity      = (float) ($attributes['gradientOpacity'] ?? 0.7);
        $gradientHeight       = (int) ($attributes['gradientHeight'] ?? 60);

        $navIconType          = $attributes['navIconType'] ?? 'arrow';
        $navIconSize          = (int) ($attributes['navIconSize'] ?? 24);
        $navIconColor         = $attributes['navIconColor'] ?? '';
        $prevIconImageUrl     = $attributes['prevIconImageUrl'] ?? '';
        $nextIconImageUrl     = $attributes['nextIconImageUrl'] ?? '';
        $prevIconSvg          = $attributes['prevIconSvg'] ?? '';
        $nextIconSvg          = $attributes['nextIconSvg'] ?? '';
        $prevIconClass        = $attributes['prevIconClass'] ?? '';
        $nextIconClass        = $attributes['nextIconClass'] ?? '';

        $navBtnWidth          = (int) ($attributes['navBtnWidth'] ?? 44);
        $navBtnHeight         = (int) ($attributes['navBtnHeight'] ?? 44);
        $navBtnBorderRadius   = (int) ($attributes['navBtnBorderRadius'] ?? 50);
        $navBtnBgColor        = $attributes['navBtnBgColor'] ?? 'rgba(0,0,0,0.7)';

        $arrowsPosition       = (string) ($attributes['arrowsPosition'] ?? 'inside');
        $alwaysShowArrows     = (bool) ($attributes['alwaysShowArrows'] ?? false);

        $styleVariation = 'default';
        if (preg_match('/is-style-(\w+)/', $className, $matches)) {
            $styleVariation = $matches[1];
        }

        $customClasses = '';
        if ($fullHeight) {
            $customClasses .= ' is-full-height';
        }
        if ($fitViewportMinusHeader) {
            $customClasses .= ' fit-vh-minus-header';
        }

        $carouselHeightVal = $fullHeight ? '100vh' : sprintf('%dpx', $height);
        $styleString = sprintf(
            '--carousel-height: %s; --carousel-min-height: %dpx; --slides-per-view-desktop: %d; --slides-per-view-tablet: %d; --slides-per-view-mobile: %d; --space-between: %dpx;',
            $carouselHeightVal,
            $minHeight,
            $slidesPerView,
            $slidesPerViewTablet,
            $slidesPerViewMobile,
            $spaceBetween
        );

        $wrapperAttributes = ['style' => $styleString];
        if ($anchor !== '') {
            $wrapperAttributes['id'] = esc_attr($anchor);
        }

        $blockWrapperAttrs = get_block_wrapper_attributes($wrapperAttributes);

        if (preg_match('/class=["\']([^"\']*)["\']/', $blockWrapperAttrs, $matches)) {
            $existingClasses = trim($matches[1]);
            $allClasses = trim($existingClasses . ' ' . $customClasses . ' ' . $styleVariation);
            $blockWrapperAttrs = preg_replace(
                '/class=["\'][^"\']*["\']/',
                'class="' . esc_attr($allClasses) . '"',
                $blockWrapperAttrs
            );
        } else {
            $blockWrapperAttrs .= ' class="' . esc_attr($customClasses . ' ' . $styleVariation) . '"';
        }

        $containerAttrs = sprintf(
            'data-slides-per-view="%d" data-slides-per-view-tablet="%d" data-slides-per-view-mobile="%d" data-space-between="%d" data-loop="%s" data-autoplay="%s" data-autoplay-delay="%d" data-speed="%d" data-navigation="%s" data-pagination="%s" data-banner-style="%s" data-banner-text-color="%s" data-banner-background-color="%s" data-banner-padding="%d" data-banner-border-radius="%d" data-carousel-height="%d" data-gradient-overlay="%s" data-gradient-color="%s" data-gradient-opacity="%s" data-gradient-height="%d" data-nav-icon-type="%s" data-nav-icon-size="%d" data-nav-icon-color="%s" data-prev-icon-image-url="%s" data-next-icon-image-url="%s" data-prev-icon-svg="%s" data-next-icon-svg="%s" data-prev-icon-class="%s" data-next-icon-class="%s" data-always-show-arrows="%s"',
            $slidesPerView,
            $slidesPerViewTablet,
            $slidesPerViewMobile,
            $spaceBetween,
            $loop ? 'true' : 'false',
            $autoplay ? 'true' : 'false',
            $autoplayDelay,
            $speed,
            $navigation ? 'true' : 'false',
            $pagination ? 'true' : 'false',
            esc_attr($bannerStyle),
            esc_attr($bannerTextColor),
            esc_attr($bannerBackgroundColor),
            $bannerPadding,
            $bannerBorderRadius,
            $height,
            $gradientOverlay ? 'true' : 'false',
            esc_attr($gradientColor),
            $gradientOpacity,
            $gradientHeight,
            esc_attr($navIconType),
            $navIconSize,
            esc_attr($navIconColor),
            esc_url($prevIconImageUrl),
            esc_url($nextIconImageUrl),
            esc_attr($prevIconSvg),
            esc_attr($nextIconSvg),
            esc_attr($prevIconClass),
            esc_attr($nextIconClass),
            $alwaysShowArrows ? 'true' : 'false'
        );

        $slidesContent   = '';
        $overlayContent  = '';
        $slideCount      = 0;

        if ($block !== null && !empty($block->inner_blocks)) {
            foreach ($block->inner_blocks as $innerBlock) {
                if (is_object($innerBlock)) {
                    $blockName  = $innerBlock->name;
                    $blockHtml  = $innerBlock->render();
                } else {
                    $blockName  = $innerBlock['blockName'] ?? '';
                    $blockHtml  = \render_block($innerBlock);
                }

                if ($blockName === 'jankx/carousel-inner-blocks-overlay') {
                    $overlayContent .= $blockHtml;
                } else {
                    $slideCount++;
                    $slidesContent .= $blockHtml;
                }
            }
        } else {
            $slidesContent = $content;
            $slideCount = substr_count($slidesContent, 'class="embla__slide"') ?: substr_count($slidesContent, 'class="wp-block-jankx-carousel-slide"');
        }

        $navEnabled = $navigation && $slideCount > 1;

        ob_start();
        ?>
        <div <?php echo $blockWrapperAttrs; ?>>
            <div class="embla" <?php echo $containerAttrs; ?> style="position:relative;">
                <div class="embla__container">
                    <?php echo $slidesContent; ?>
                </div>

                <?php echo $overlayContent; ?>

                <?php if ($navEnabled && $arrowsPosition === 'inside') : ?>
                    <div class="carousel-arrows">
                        <button class="carousel-arrow carousel-arrow--prev" aria-label="Previous slide">
                            <?php echo $this->renderNavIcon($navIconType, $prevIconSvg, $prevIconClass, $navIconColor, $navIconSize); ?>
                        </button>
                        <button class="carousel-arrow carousel-arrow--next" aria-label="Next slide">
                            <?php echo $this->renderNavIcon($navIconType, $nextIconSvg, $nextIconClass, $navIconColor, $navIconSize); ?>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($pagination && $slideCount > 1) : ?>
                    <div class="embla__dots" style="position:absolute;bottom:12px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:2;"></div>
                <?php endif; ?>
            </div>

            <?php if ($navEnabled && $arrowsPosition === 'bottom') : ?>
                <div class="carousel-arrows-bottom-row">
                    <button class="carousel-arrow carousel-arrow--prev" aria-label="Previous slide">
                        <?php echo $this->renderNavIcon($navIconType, $prevIconSvg, $prevIconClass, $navIconColor, $navIconSize); ?>
                    </button>
                    <button class="carousel-arrow carousel-arrow--next" aria-label="Next slide">
                        <?php echo $this->renderNavIcon($navIconType, $nextIconSvg, $nextIconClass, $navIconColor, $navIconSize); ?>
                    </button>
                </div>
            <?php elseif ($navEnabled && $arrowsPosition === 'outside') : ?>
                <div class="carousel-arrows">
                    <button class="carousel-arrow carousel-arrow--prev" aria-label="Previous slide">
                        <?php echo $this->renderNavIcon($navIconType, $prevIconSvg, $prevIconClass, $navIconColor, $navIconSize); ?>
                    </button>
                    <button class="carousel-arrow carousel-arrow--next" aria-label="Next slide">
                        <?php echo $this->renderNavIcon($navIconType, $nextIconSvg, $nextIconClass, $navIconColor, $navIconSize); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function renderNavIcon(string $type, string $svg, string $iconClass, string $color, int $size): string
    {
        if ($svg !== '') {
            return $svg;
        }
        if ($iconClass !== '') {
            return sprintf('<i class="%s" style="font-size:%dpx;%s"></i>',
                esc_attr($iconClass),
                $size,
                $color !== '' ? 'color:' . esc_attr($color) . ';' : ''
            );
        }
        return sprintf('<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="%s"><path d="M15 18l-6-6 6-6"/></svg>',
            $size,
            $size,
            $color !== '' ? 'color:' . esc_attr($color) . ';' : ''
        );
    }
}
