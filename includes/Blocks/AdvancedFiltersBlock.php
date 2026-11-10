<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function is_array;
use function sprintf;

class AdvancedFiltersBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/advanced-filters';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if (!$this->shouldRender($attributes)) {
            return '';
        }

        $blockId          = $attributes['blockId'] ?? '';
        $targetBlockIds   = $attributes['targetBlockIds'] ?? [];
        $layout           = $attributes['layout'] ?? 'vertical';
        $targetPostType   = $attributes['targetPostType'] ?? '';
        $showLabels       = $attributes['showLabels'] ?? true;
        $showResetBtn     = $attributes['showResetButton'] ?? false;
        $resetBtnText     = $attributes['resetButtonText'] ?? __('Reset', 'timeless');
        $ajaxEnabled      = $attributes['ajaxEnabled'] ?? false;
        $updateUrl        = $attributes['updateUrl'] ?? false;
        $scrollToResults  = $attributes['scrollToResults'] ?? false;

        $wrapperClasses = $this->getBlockClass('jankx-advanced-filters');
        $wrapperClasses .= ' layout-' . esc_attr($layout);
        if ($ajaxEnabled) {
            $wrapperClasses .= ' is-ajax';
        }
        if ($updateUrl) {
            $wrapperClasses .= ' is-update-url';
        }
        if ($scrollToResults) {
            $wrapperClasses .= ' is-scroll-to-results';
        }

        $dataAttrs = '';
        if ($blockId !== '') {
            $dataAttrs .= sprintf(' data-block-id="%s"', esc_attr($blockId));
        }
        if (is_array($targetBlockIds) && count($targetBlockIds) > 0) {
            $dataAttrs .= sprintf(' data-target-block-ids="%s"', esc_attr(implode(',', $targetBlockIds)));
        }
        if ($targetPostType !== '') {
            $dataAttrs .= sprintf(' data-target-post-type="%s"', esc_attr($targetPostType));
        }
        $dataAttrs .= sprintf(' data-ajax-enabled="%s"', esc_attr($ajaxEnabled ? 'true' : 'false'));
        $dataAttrs .= sprintf(' data-update-url="%s"', esc_attr($updateUrl ? 'true' : 'false'));
        $dataAttrs .= sprintf(' data-scroll-to-results="%s"', esc_attr($scrollToResults ? 'true' : 'false'));

        $resetHtml = '';
        if ($showResetBtn) {
            $resetHtml = sprintf(
                '<button class="jankx-advanced-filters__reset-btn">%s</button>',
                esc_html($resetBtnText)
            );
        }

        $innerHtml = wp_kses_post($content);

        return sprintf(
            '<div class="%s"%s>%s%s</div>',
            esc_attr($wrapperClasses),
            $dataAttrs,
            $resetHtml,
            $innerHtml
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
