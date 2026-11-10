<?php

namespace Timeless\Blocks;

use function apply_filters;
use function esc_attr;
use function esc_html;

class LayoutSwitcherBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/layout-switcher';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if (is_admin()) {
            return '<div class="jankx-layout-switcher-placeholder">' . esc_html__('Layout Switcher - preview available on frontend.', 'timeless') . '</div>';
        }

        $queryId = $block->context['queryId'] ?? '';
        $postType = $block->context['postType'] ?? 'post';
        $currentLayout = $block->context['displayLayout'] ?? 'grid';

        if ($queryId === '') {
            return '';
        }

        $availableLayouts = apply_filters('timeless_available_layouts', [], $postType);

        if (empty($availableLayouts)) {
            return '';
        }

        $supportedLayouts = $attributes['supportedLayouts'] ?? [];
        if (empty($supportedLayouts)) {
            $supportedLayouts = array_keys($availableLayouts);
        }

        $layoutsToShow = array_intersect_key($availableLayouts, array_flip($supportedLayouts));

        if (empty($layoutsToShow)) {
            return '';
        }

        $displayType = $attributes['displayType'] ?? 'icons';
        $alignment = $attributes['alignment'] ?? 'left';

        ob_start();
        ?>
        <div class="jankx-layout-switcher layout-switcher--align-<?php echo esc_attr($alignment); ?> layout-switcher--type-<?php echo esc_attr($displayType); ?>"
             data-target-query-id="<?php echo esc_attr($queryId); ?>">
            <ul class="layout-options">
                <?php foreach ($layoutsToShow as $name => $info): ?>
                    <?php
                    $title = is_array($info) ? ($info['title'] ?? $name) : esc_html($name);
                    $icon = is_array($info) ? ($info['icon'] ?? '') : '';
                    $activeClass = ($currentLayout === $name) ? 'is-active' : '';
                    ?>
                    <li class="layout-option <?php echo esc_attr($activeClass); ?>" data-layout="<?php echo esc_attr($name); ?>">
                        <button type="button" aria-pressed="<?php echo ($currentLayout === $name) ? 'true' : 'false'; ?>">
                            <?php if ($displayType !== 'labels'): ?>
                                <span class="layout-icon"><?php echo $icon; ?></span>
                            <?php endif; ?>

                            <?php if ($displayType !== 'icons'): ?>
                                <span class="layout-label"><?php echo esc_html($title); ?></span>
                            <?php endif; ?>

                            <span class="screen-reader-text"><?php echo esc_html($title); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
