<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function get_the_ID;
use function get_post_type;
use function is_admin;
use function sanitize_html_class;
use function sprintf;
use function wp_kses_post;

class SmartTabBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/smart-tab';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $trigger = $attributes['trigger'] ?? 'manual';
        $tabId = $attributes['tabId'] ?? '';
        $triggerSettings = $attributes['triggerSettings'] ?? [];
        $contentTextColor = $attributes['contentTextColor'] ?? '';
        $contentGradient = $attributes['contentGradient'] ?? '';
        $contentBackgroundColor = $attributes['contentBackgroundColor'] ?? '';

        $classes = ['smart-tab'];
        if ($tabId !== '') {
            $classes[] = sanitize_html_class($tabId);
        }

        $wrapperAttributes = [
            'class' => implode(' ', $classes),
            'data-trigger' => esc_attr($trigger),
        ];

        if ($triggerSettings !== [] && is_array($triggerSettings)) {
            $wrapperAttributes['data-trigger-settings'] = esc_attr(wp_json_encode($triggerSettings));
        }

        $wrapperAttributes = get_block_wrapper_attributes($wrapperAttributes);

        $contentStyles = [];
        if ($contentTextColor !== '') {
            $contentStyles[] = 'color:' . esc_attr($contentTextColor);
        }
        if ($contentGradient !== '') {
            $contentStyles[] = 'background:' . esc_attr($contentGradient);
        } elseif ($contentBackgroundColor !== '') {
            $contentStyles[] = 'background-color:' . esc_attr($contentBackgroundColor);
        }

        $contentStyleAttr = !empty($contentStyles) ? ' style="' . esc_attr(implode('; ', $contentStyles)) . '"' : '';
        $contentWrapper = sprintf('<div class="smart-tab__content"%s>%s</div>', $contentStyleAttr, wp_kses_post($content));

        return sprintf('<div%s>%s</div>', $wrapperAttributes, $contentWrapper);
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
