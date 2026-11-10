<?php

namespace Timeless\Blocks;

use function absint;
use function esc_attr;
use function esc_html;
use function esc_url;
use function implode_arr;
use function is_array;
use function sanitize_html_class;
use function sprintf;
use function trim;
use function uniqid;

class OffcanvasSidebarBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/offcanvas-sidebar';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $sidebarPosition = $attributes['sidebarPosition'] ?? 'left';
        $animationEffect = $attributes['animationEffect'] ?? 'slide-in';
        $sidebarWidth = $attributes['sidebarWidth'] ?? '300px';
        $overlayColor = $attributes['overlayColor'] ?? 'rgba(0,0,0,0.2)';
        $showOverlay = $attributes['showOverlay'] ?? true;
        $closeOnOverlayClick = $attributes['closeOnOverlayClick'] ?? true;
        $closeOnEscape = $attributes['closeOnEscape'] ?? true;
        $autoClose = $attributes['autoClose'] ?? false;
        $autoCloseDelay = (int) ($attributes['autoCloseDelay'] ?? 5000);
        $showCloseButton = $attributes['showCloseButton'] ?? true;
        $closeButtonPosition = $attributes['closeButtonPosition'] ?? 'top-right';
        $closeButtonSize = $attributes['closeButtonSize'] ?? 'medium';
        $closeButtonStyle = $attributes['closeButtonStyle'] ?? 'circle';
        $closeButtonColor = $attributes['closeButtonColor'] ?? 'inherit';
        $closeButtonOffsetTop = $attributes['closeButtonOffsetTop'] ?? '';
        $closeButtonOffsetRight = $attributes['closeButtonOffsetRight'] ?? '';
        $closeButtonOffsetBottom = $attributes['closeButtonOffsetBottom'] ?? '';
        $closeButtonOffsetLeft = $attributes['closeButtonOffsetLeft'] ?? '';
        $className = $attributes['className'] ?? '';

        $blockId = $attributes['anchor'] ?? ('offcanvas-sidebar-' . uniqid());

        $wrapperClasses = ['offcanvas-sidebar-block'];
        if ($className !== '') {
            $wrapperClasses[] = esc_attr($className);
        }

        $align = $attributes['align'] ?? '';
        if ($align !== '') {
            $wrapperClasses[] = 'align' . sanitize_html_class($align);
        }

        $backgroundColor = $attributes['backgroundColor'] ?? '';
        $textColor = $attributes['textColor'] ?? '';
        $gradient = $attributes['gradient'] ?? '';

        $sidebarClasses = ['offcanvas-sidebar'];
        if ($backgroundColor !== '') {
            $sidebarClasses[] = 'has-' . esc_attr($backgroundColor) . '-background-color';
            $sidebarClasses[] = 'has-background';
        }
        if ($textColor !== '') {
            $sidebarClasses[] = 'has-' . esc_attr($textColor) . '-color';
            $sidebarClasses[] = 'has-text-color';
        }
        if ($gradient !== '') {
            $sidebarClasses[] = 'has-' . esc_attr($gradient) . '-gradient-background';
            $sidebarClasses[] = 'has-background';
        }

        $sidebarStyle = 'width:' . esc_attr($sidebarWidth) . ';';
        if (!empty($attributes['style']['spacing']['margin'])) {
            $margin = $attributes['style']['spacing']['margin'];
            if (is_array($margin)) {
                foreach (['top', 'right', 'bottom', 'left'] as $side) {
                    if (!empty($margin[$side])) {
                        $sidebarStyle .= sprintf(' margin-%s:%s;', $side, esc_attr($margin[$side]));
                    }
                }
            }
        }
        if (isset($attributes['style']['color']['background'])) {
            $sidebarStyle .= sprintf(' background-color:%s;', esc_attr($attributes['style']['color']['background']));
        }
        if (isset($attributes['style']['color']['gradient'])) {
            $sidebarStyle .= sprintf(' background:%s;', esc_attr($attributes['style']['color']['gradient']));
        }

        $overlayStyle = sprintf('background-color:%s;', esc_attr($overlayColor));

        $overlay = $showOverlay ? sprintf(
            '<div class="offcanvas-overlay" style="%s" data-target="%s"></div>',
            esc_attr($overlayStyle),
            esc_attr($blockId)
        ) : '';

        $closeButton = '';
        if ($showCloseButton) {
            $closeButtonClasses = ['close-button', 'position-' . esc_attr($closeButtonPosition), 'size-' . esc_attr($closeButtonSize), 'style-' . esc_attr($closeButtonStyle)];
            $closeButtonStyleAttr = 'color:' . esc_attr($closeButtonColor === 'inherit' ? $textColor : $closeButtonColor) . ';';
            if ($closeButtonOffsetTop !== '') {
                $closeButtonStyleAttr .= sprintf(' top:%s;', esc_attr($closeButtonOffsetTop));
            }
            if ($closeButtonOffsetRight !== '') {
                $closeButtonStyleAttr .= sprintf(' right:%s;', esc_attr($closeButtonOffsetRight));
            }
            if ($closeButtonOffsetBottom !== '') {
                $closeButtonStyleAttr .= sprintf(' bottom:%s;', esc_attr($closeButtonOffsetBottom));
            }
            if ($closeButtonOffsetLeft !== '') {
                $closeButtonStyleAttr .= sprintf(' left:%s;', esc_attr($closeButtonOffsetLeft));
            }
            $closeButton = sprintf(
                '<button class="%s" data-target="%s" type="button" style="%s" aria-label="%s">×</button>',
                esc_attr(implode(' ', $closeButtonClasses)),
                esc_attr($blockId),
                esc_attr($closeButtonStyleAttr),
                esc_attr__('Close sidebar', 'timeless')
            );
        }

        $dataAttrs = [];
        $dataAttrs[] = sprintf('data-show-overlay="%s"', $showOverlay ? 'true' : 'false');
        $dataAttrs[] = sprintf('data-close-on-overlay-click="%s"', $closeOnOverlayClick ? 'true' : 'false');
        $dataAttrs[] = sprintf('data-close-on-escape="%s"', $closeOnEscape ? 'true' : 'false');
        $dataAttrs[] = sprintf('data-auto-close="%s"', $autoClose ? 'true' : 'false');
        $dataAttrs[] = sprintf('data-auto-close-delay="%d"', $autoCloseDelay);

        $containerClasses = ['offcanvas-sidebar-container', 'effect-' . sanitize_html_class($animationEffect), 'position-' . sanitize_html_class($sidebarPosition)];

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapperClasses)); ?>" id="<?php echo esc_attr($blockId); ?>" <?php echo implode(' ', $dataAttrs); ?>>
            <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>" data-effect="<?php echo esc_attr($animationEffect); ?>">
                <?php echo $overlay; ?>
                <div class="<?php echo esc_attr(implode(' ', $sidebarClasses)); ?>" style="<?php echo esc_attr($sidebarStyle); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Sidebar navigation', 'timeless'); ?>" data-lenis-prevent>
                    <?php echo $closeButton; ?>
                    <div class="sidebar-content" data-lenis-prevent>
                        <?php echo wp_kses_post($content); ?>
                    </div>
                </div>
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
