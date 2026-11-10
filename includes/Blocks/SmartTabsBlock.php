<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function in_array;
use function implode;
use function is_array;
use function sanitize_html_class;
use function sprintf;
use function strpos;
use function wp_json_encode;
use function wp_kses_post;

class SmartTabsBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/smart-tabs';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $tabType = $attributes['tabType'] ?? 'horizontal';
        $styleType = $attributes['styleType'] ?? 'default';
        $activeTab = max(0, (int) ($attributes['activeTab'] ?? 0));
        $tabAlignment = $attributes['tabAlignment'] ?? 'left';
        $hideTabsBorderBottom = $attributes['hideTabsBorderBottom'] ?? false;
        $centerNavigation = $attributes['centerNavigation'] ?? false;
        $hideTabContent = $attributes['hideTabContent'] ?? false;
        $label = $attributes['label'] ?? '';
        $showLabel = $attributes['showLabel'] ?? false;
        $className = $attributes['className'] ?? '';
        $anchor = $attributes['anchor'] ?? '';

        $wrapperClasses = array_filter([
            'smart-tabs',
            'smart-tabs--' . sanitize_html_class($tabType),
            'smart-tabs--style-' . sanitize_html_class($styleType),
            $hideTabsBorderBottom ? 'smart-tabs--hide-border-bottom' : '',
            $centerNavigation ? 'smart-tabs--center-navigation' : '',
            $hideTabContent ? 'smart-tabs--hide-content' : '',
            $className,
        ]);

        $wrapperAttributes = [
            'class' => implode(' ', $wrapperClasses),
            'data-active-tab' => (string) $activeTab,
        ];

        if ($anchor !== '') {
            $wrapperAttributes['id'] = esc_attr($anchor);
        }

        $innerBlocks = $block && isset($block->parsed_block['innerBlocks'])
            ? $block->parsed_block['innerBlocks']
            : [];

        $tabNavHtml = $this->renderTabNavigation($innerBlocks, $activeTab, $tabAlignment, $attributes);

        $labelHtml = '';
        if ($showLabel && $label !== '') {
            $labelHtml = sprintf('<div class="smart-tabs__label">%s</div>', esc_html($label));
        }

        $attrsString = '';
        foreach ($wrapperAttributes as $key => $value) {
            $attrsString .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }

        $navigationHtml = sprintf(
            '<div class="smart-tabs__navigation">%s%s</div>',
            $labelHtml,
            $tabNavHtml
        );

        if ($hideTabContent) {
            return sprintf('<div%s>%s</div>', $attrsString, $navigationHtml);
        }

        return sprintf(
            '<div%s>%s<div class="smart-tabs__content">%s</div></div>',
            $attrsString,
            $navigationHtml,
            wp_kses_post($content)
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    protected function renderTabNavigation(array $innerBlocks, int $activeTab, string $tabAlignment = 'left', array $parentAttributes = []): string
    {
        if (empty($innerBlocks)) {
            return '';
        }

        $tabType = (string) ($parentAttributes['tabType'] ?? 'horizontal');

        $parentTabItemTextColor = $parentAttributes['tabItemTextColor'] ?? '';
        $parentTabItemBgColor = $parentAttributes['tabItemBackgroundColor'] ?? '';
        $parentTabItemGradient = $parentAttributes['tabItemGradient'] ?? '';
        $parentActiveTabTextColor = $parentAttributes['activeTabTextColor'] ?? '';
        $parentActiveTabBgColor = $parentAttributes['activeTabBackgroundColor'] ?? '';
        $parentActiveTabGradient = $parentAttributes['activeTabGradient'] ?? '';
        $parentActiveBorderColor = $parentAttributes['activeTabBorderColor'] ?? '';
        $parentActiveBorderStyle = $parentAttributes['activeTabBorderStyle'] ?? '';
        $parentActiveBorderWidth = $parentAttributes['activeTabBorderWidth'] ?? '';

        $navItems = [];
        $tabIndex = 0;

        foreach ($innerBlocks as $block) {
            if (!is_array($block) || ($block['blockName'] ?? '') !== 'jankx/smart-tab') {
                continue;
            }

            $blockAttrs = $block['attrs'] ?? [];
            $triggerKey = $blockAttrs['trigger'] ?? 'manual';
            $title = $blockAttrs['title'] ?? sprintf(__('Tab %d', 'timeless'), $tabIndex + 1);
            $iconType = $blockAttrs['iconType'] ?? 'none';
            $icon = $blockAttrs['icon'] ?? '';
            $iconPosition = $blockAttrs['iconPosition'] ?? 'before';
            $iconSize = $blockAttrs['iconSize'] ?? '16px';
            $iconColor = $blockAttrs['iconColor'] ?? '';

            $isActive = $tabIndex === $activeTab;
            $itemClasses = ['smart-tabs__nav-item'];
            if ($isActive) {
                $itemClasses[] = 'is-active';
            }

            $tabStyles = [];

            $textColor = $blockAttrs['normalTabTextColor'] ?? $parentTabItemTextColor;
            $gradient = $blockAttrs['normalTabGradient'] ?? $parentTabItemGradient;
            $bgColor = $blockAttrs['normalTabBackgroundColor'] ?? $parentTabItemBgColor;

            if ($textColor !== '') {
                $tabStyles[] = sprintf('--smart-tabs-item-color: %s', esc_attr($textColor));
            }
            if ($gradient !== '') {
                $tabStyles[] = sprintf('--smart-tabs-item-bg: %s', esc_attr($gradient));
            } elseif ($bgColor !== '') {
                $tabStyles[] = sprintf('--smart-tabs-item-bg: %s', esc_attr($bgColor));
            }

            $activeTextColor = $blockAttrs['activeTabTextColor'] ?? $parentActiveTabTextColor;
            $activeGradient = $blockAttrs['activeTabGradient'] ?? $parentActiveTabGradient;
            $activeBgColor = $blockAttrs['activeTabBackgroundColor'] ?? $parentActiveTabBgColor;

            if ($activeTextColor !== '') {
                $tabStyles[] = sprintf('--smart-tabs-active-color: %s', esc_attr($activeTextColor));
            }
            if ($activeGradient !== '') {
                $tabStyles[] = sprintf('--smart-tabs-active-bg: %s', esc_attr($activeGradient));
            } elseif ($activeBgColor !== '') {
                $tabStyles[] = sprintf('--smart-tabs-active-bg: %s', esc_attr($activeBgColor));
            }

            if ($parentActiveBorderColor !== '' && $parentActiveBorderStyle !== 'none') {
                $activeBorderWidth = $parentActiveBorderWidth !== '' ? $parentActiveBorderWidth : '3px';
                $activeBorderStyle = $parentActiveBorderStyle !== '' ? $parentActiveBorderStyle : 'solid';
                $tabStyles[] = sprintf('--smart-tabs-active-border-color: %s', esc_attr($parentActiveBorderColor));
                $tabStyles[] = sprintf('--smart-tabs-active-border-style: %s', esc_attr($activeBorderStyle));
                $tabStyles[] = sprintf('--smart-tabs-active-border-width: %s', esc_attr($activeBorderWidth));
            }

            $tabStyleAttr = !empty($tabStyles) ? ' style="' . esc_attr(implode('; ', $tabStyles)) . '"' : '';

            $iconHtml = '';
            if ($iconType !== 'none' && $icon !== '') {
                $iconStyles = [];
                if ($iconSize !== '') {
                    $iconStyles[] = sprintf('font-size: %s', esc_attr($iconSize));
                }
                if ($iconColor !== '') {
                    $iconStyles[] = sprintf('color: %s', esc_attr($iconColor));
                }
                $iconStyleAttr = !empty($iconStyles) ? ' style="' . esc_attr(implode('; ', $iconStyles)) . '"' : '';
                $iconHtml = sprintf(
                    '<span class="smart-tabs__nav-icon"%s>%s</span>',
                    $iconStyleAttr,
                    wp_kses_post($icon)
                );
            }

            $labelHtml = sprintf('<span class="smart-tabs__nav-label">%s</span>', esc_html($title));

            $contentHtml = $iconPosition === 'after' ? $labelHtml . $iconHtml : $iconHtml . $labelHtml;

            $additionalDataAttrs = '';

            if ($triggerKey === 'advanced-filter') {
                $triggerSettings = $blockAttrs['triggerSettings'] ?? [];
                $targetBlockId = $triggerSettings['targetBlockId'] ?? '';
                if ($targetBlockId !== '') {
                    $additionalDataAttrs .= sprintf(' data-target-block-id="%s"', esc_attr($targetBlockId));
                }
                $targetBlockIds = $triggerSettings['targetBlockIds'] ?? [];
                if (is_array($targetBlockIds) && !empty($targetBlockIds)) {
                    $additionalDataAttrs .= sprintf(' data-target-block-ids="%s"', esc_attr(wp_json_encode(array_values(array_map('strval', $targetBlockIds)))));
                }
            } elseif ($triggerKey === 'open-link') {
                $triggerSettings = $blockAttrs['triggerSettings'] ?? [];
                $href = $triggerSettings['url'] ?? '#';
                $target = $triggerSettings['target'] ?? '_self';
                $rel = $triggerSettings['rel'] ?? '';
                $relAttr = $rel !== '' ? sprintf(' rel="%s"', esc_attr($rel)) : '';
                if ($href === '') {
                    $href = '#';
                }

                $navItems[] = sprintf(
                    '<a class="%s" role="tab" aria-selected="%s" tabindex="%s" data-tab-index="%d" data-trigger="%s" href="%s" target="%s"%s%s>%s</a>',
                    implode(' ', $itemClasses),
                    $isActive ? 'true' : 'false',
                    $isActive ? '0' : '-1',
                    $tabIndex,
                    esc_attr($triggerKey),
                    esc_url($href),
                    esc_attr($target),
                    $relAttr,
                    $tabStyleAttr,
                    $contentHtml
                );
            } else {
                $navItems[] = sprintf(
                    '<button class="%s" role="tab" aria-selected="%s" tabindex="%s" data-tab-index="%d" data-trigger="%s" type="button"%s%s>%s</button>',
                    implode(' ', $itemClasses),
                    $isActive ? 'true' : 'false',
                    $isActive ? '0' : '-1',
                    $tabIndex,
                    esc_attr($triggerKey),
                    $tabStyleAttr,
                    $additionalDataAttrs,
                    $contentHtml
                );
            }

            $tabIndex++;
        }

        return sprintf(
            '<div class="smart-tabs__nav-list align-%s" role="tablist">%s</div>',
            esc_attr($tabAlignment),
            implode('', $navItems)
        );
    }
}
