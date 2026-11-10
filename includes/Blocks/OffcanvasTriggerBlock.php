<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function sanitize_html_class;
use function sprintf;

class OffcanvasTriggerBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/offcanvas-trigger';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $targetSidebarId = $attributes['targetSidebarId'] ?? '';
        $animationSkin = $attributes['animationSkin'] ?? 'hamburger-to-x';
        $barColor = $attributes['barColor'] ?? '#333333';
        $barThickness = (int) ($attributes['barThickness'] ?? 3);
        $barWidth = (int) ($attributes['barWidth'] ?? 30);
        $barSpacing = (int) ($attributes['barSpacing'] ?? 5);
        $barLengths = $attributes['barLengths'] ?? 'equal';
        $displayOn = $attributes['displayOn'] ?? 'all';
        $className = $attributes['className'] ?? '';

        $getBarWidth = function (string $barType) use ($barWidth, $barLengths): int {
            switch ($barLengths) {
                case 'long-short-long':
                    return $barType === 'middle' ? (int) ($barWidth * 0.6) : $barWidth;
                case 'progressive':
                    return match ($barType) {
                        'top' => $barWidth,
                        'middle' => (int) ($barWidth * 0.8),
                        default => (int) ($barWidth * 0.6),
                    };
                default:
                    return $barWidth;
            }
        };

        $topBarStyle = sprintf(
            'background-color:%s;height:%dpx;width:%dpx;',
            esc_attr($barColor),
            $barThickness,
            (int) $getBarWidth('top')
        );

        $middleBarStyle = sprintf(
            'background-color:%s;height:%dpx;width:%dpx;',
            esc_attr($barColor),
            $barThickness,
            (int) $getBarWidth('middle')
        );

        $bottomBarStyle = sprintf(
            'background-color:%s;height:%dpx;width:%dpx;',
            esc_attr($barColor),
            $barThickness,
            (int) $getBarWidth('bottom')
        );

        $containerStyle = sprintf(
            '--bar-spacing:%dpx;--bar-thickness:%dpx;--bar-width:%dpx;--bar-color:%s;',
            $barSpacing,
            $barThickness,
            $barWidth,
            esc_attr($barColor)
        );

        $classes = array_filter([
            'offcanvas-trigger-block',
            'display-' . sanitize_html_class($displayOn),
            $className,
        ]);

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <button
                class="offcanvas-trigger hamburger-trigger"
                data-target-sidebar="<?php echo esc_attr($targetSidebarId); ?>"
                aria-label="<?php echo esc_attr__('Toggle menu', 'timeless'); ?>"
            >
                <div class="hamburger-container skin-<?php echo esc_attr($animationSkin); ?> lengths-<?php echo esc_attr($barLengths); ?>" style="<?php echo esc_attr($containerStyle); ?>">
                    <span class="bar bar-top" style="<?php echo esc_attr($topBarStyle); ?>"></span>
                    <span class="bar bar-middle" style="<?php echo esc_attr($middleBarStyle); ?>"></span>
                    <span class="bar bar-bottom" style="<?php echo esc_attr($bottomBarStyle); ?>"></span>
                </div>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
