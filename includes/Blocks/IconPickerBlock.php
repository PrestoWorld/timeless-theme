<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function sprintf;

class IconPickerBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/icon-picker';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $iconName      = $attributes['iconName'] ?? '';
        $iconType      = $attributes['iconType'] ?? 'material';
        $iconCategory  = $attributes['iconCategory'] ?? '';
        $iconSize      = $attributes['iconSize'] ?? '24px';
        $iconColor     = $attributes['iconColor'] ?? '#333333';
        $iconAlignment = $attributes['iconAlignment'] ?? 'left';
        $iconStyle     = $attributes['iconStyle'] ?? 'filled';
        $linkUrl       = $attributes['linkUrl'] ?? '';
        $linkTarget    = $attributes['linkTarget'] ?? '_self';
        $linkRel       = $attributes['linkRel'] ?? '';
        $showLabel     = $attributes['showLabel'] ?? false;
        $iconLabel     = $attributes['iconLabel'] ?? '';
        $labelPosition = $attributes['labelPosition'] ?? 'after';
        $customClassName = $attributes['customClassName'] ?? '';

        if ($iconName === '') {
            return '<div class="jankx-icon-picker-placeholder"><p>' . esc_html__('Select an icon to display.', 'timeless') . '</p></div>';
        }

        $iconHtml = $this->renderIcon($iconName, $iconType, $iconCategory, $iconSize, $iconColor, $iconStyle);

        $labelHtml = '';
        if ($showLabel && $iconLabel !== '') {
            $labelHtml = sprintf(
                '<span class="jankx-icon-picker-block__label jankx-icon-picker-block__label--%s">%s</span>',
                esc_attr($labelPosition),
                esc_html($iconLabel)
            );
        }

        $contentHtml = $iconHtml . $labelHtml;

        if ($linkUrl !== '') {
            $rel = $linkRel !== '' ? ' rel="' . esc_attr($linkRel) . '"' : '';
            $contentHtml = sprintf(
                '<a href="%s" target="%s"%s class="jankx-icon-picker-block__link">%s</a>',
                esc_url($linkUrl),
                esc_attr($linkTarget),
                $rel,
                $contentHtml
            );
        }

        $classes = array_filter([
            'jankx-icon-picker-block',
            'jankx-icon-picker-block--' . sanitize_html_class($iconAlignment),
            $customClassName,
        ]);

        return sprintf(
            '<div class="%s"><div class="jankx-icon-picker-block__content" style="text-align:%s;">%s</div></div>',
            esc_attr(implode(' ', $classes)),
            esc_attr($iconAlignment),
            $contentHtml
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function renderIcon(string $iconName, string $iconType, string $iconCategory, string $iconSize, string $iconColor, string $iconStyle): string
    {
        $style = sprintf('font-size:%s;color:%s;', esc_attr($iconSize), esc_attr($iconColor));

        if ($iconType === 'material') {
            $styleClass = $iconStyle !== 'filled' ? 'material-icons-' . esc_attr($iconStyle) : 'material-icons';
            return sprintf(
                '<span class="%s" style="%s" aria-hidden="true">%s</span>',
                esc_attr($styleClass),
                $style,
                esc_html($iconName)
            );
        }

        if ($iconType === 'fontawesome') {
            $prefix = $iconCategory === 'brands' ? 'fab' :
                ($iconCategory === 'regular' ? 'far' : 'fas');
            return sprintf(
                '<i class="%s fa-%s" style="%s" aria-hidden="true"></i>',
                esc_attr($prefix),
                esc_attr($iconName),
                $style
            );
        }

        if ($iconType === 'custom') {
            return sprintf(
                '<span class="icon icon-%s" style="%s" aria-hidden="true"></span>',
                esc_attr($iconName),
                $style
            );
        }

        return '';
    }
}
