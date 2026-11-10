<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function get_block_wrapper_attributes;
use function preg_match;

class SvgIconBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/svg-icon';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $icon = $attributes['icon'] ?? '';
        $iconName = $attributes['iconName'] ?? '';
        $itemsJustification = $attributes['itemsJustification'] ?? '';
        $iconBackgroundColor = $attributes['iconBackgroundColor'] ?? '';
        $iconBackgroundColorValue = $attributes['iconBackgroundColorValue'] ?? '';
        $iconColor = $attributes['iconColor'] ?? '';
        $iconColorValue = $attributes['iconColorValue'] ?? '';
        $gradient = $attributes['gradient'] ?? '';
        $customGradient = $attributes['customGradient'] ?? '';
        $hasNoIconFill = !empty($attributes['hasNoIconFill']);
        $label = $attributes['label'] ?? '';
        $title = $attributes['title'] ?? '';
        $linkUrl = $attributes['linkUrl'] ?? '';
        $linkRel = $attributes['linkRel'] ?? '';
        $linkTarget = $attributes['linkTarget'] ?? '';
        $rotate = $attributes['rotate'] ?? 0;
        $flipHorizontal = !empty($attributes['flipHorizontal']);
        $flipVertical = !empty($attributes['flipVertical']);
        $width = $attributes['width'] ?? '';
        $height = $attributes['height'] ?? '';

        $printedIcon = '';
        if (!empty($icon)) {
            $printedIcon = $icon;
        } elseif (!empty($iconName)) {
            $printedIcon = '<svg class="iconfont icon-' . esc_attr($iconName) . '" aria-hidden="true"></svg>';
        }

        if (empty($printedIcon)) {
            return $content;
        }

        $iconClasses = ['icon-container'];
        if ($iconColorValue) {
            $iconClasses[] = 'has-icon-color';
        }
        if ($hasNoIconFill) {
            $iconClasses[] = 'has-no-icon-fill-color';
        }
        if ($iconBackgroundColorValue || $iconBackgroundColor || $gradient || $customGradient) {
            $iconClasses[] = 'has-icon-background-color';
        }
        if ($iconBackgroundColor) {
            $iconClasses[] = "has-{$iconBackgroundColor}-background-color";
        }
        if ($iconColor) {
            $iconClasses[] = "has-{$iconColor}-color";
        }
        if ($gradient) {
            $iconClasses[] = "has-{$gradient}-gradient-background";
        }

        $iconClassString = implode(' ', $iconClasses);

        $iconStyles = [];
        if (!$gradient && $customGradient) {
            $iconStyles['background'] = $customGradient;
        }
        if ($iconBackgroundColorValue) {
            $iconStyles['background-color'] = $iconBackgroundColorValue;
        }
        if ($iconColorValue) {
            $iconStyles['color'] = $iconColorValue;
        }

        $iconWidth = empty($height) ? '48px' : '';
        if (!empty($width)) {
            if (is_numeric($width)) {
                $iconWidth = "{$width}px";
            } elseif (preg_match('/^(\d+(?:\.\d+)?)([a-zA-Z%]*)$/', $width, $matches)) {
                $iconWidth = $matches[2] ? $width : "{$width}px";
            } else {
                $iconWidth = $width;
            }
        }
        if ($iconWidth) {
            $iconStyles['width'] = $iconWidth;
        }
        if (!empty($height)) {
            if (is_numeric($height)) {
                $iconStyles['height'] = "{$height}px";
            } else {
                $iconStyles['height'] = $height;
            }
        }

        $rotateValue = $rotate ? "{$rotate}deg" : '0deg';
        $scaleXValue = $flipHorizontal ? '-1' : '1';
        $scaleYValue = $flipVertical ? '-1' : '1';
        $iconStyles['transform'] = "rotate({$rotateValue}) scaleX({$scaleXValue}) scaleY({$scaleYValue})";

        $iconStyleString = '';
        foreach ($iconStyles as $key => $val) {
            $iconStyleString .= "{$key}: {$val}; ";
        }

        $wrapperClasses = [];
        if ($itemsJustification) {
            $wrapperClasses[] = "items-justified-{$itemsJustification}";
        }
        $wrapperAttrs = get_block_wrapper_attributes(['class' => implode(' ', $wrapperClasses)]);

        $titleAttr = $title ? ' title="' . esc_attr($title) . '"' : '';

        $innerAttrs = '';
        if ($linkUrl) {
            $innerAttrs .= ' href="' . esc_url($linkUrl) . '"';
            if ($linkTarget) {
                $innerAttrs .= ' target="' . esc_attr($linkTarget) . '"';
            }
            if ($linkRel) {
                $innerAttrs .= ' rel="' . esc_attr($linkRel) . '"';
            }
        }
        if ($label) {
            $innerAttrs .= ' aria-label="' . esc_attr($label) . '"';
        }

        ob_start();
        ?>
        <div <?php echo $wrapperAttrs; ?><?php echo $titleAttr; ?>>
            <?php if ($linkUrl) : ?>
                <a class="<?php echo esc_attr($iconClassString); ?>" style="<?php echo esc_attr($iconStyleString); ?>"<?php echo $innerAttrs; ?>>
                    <?php echo $printedIcon; ?>
                </a>
            <?php else : ?>
                <div class="<?php echo esc_attr($iconClassString); ?>" style="<?php echo esc_attr($iconStyleString); ?>"<?php echo $innerAttrs; ?>>
                    <?php echo $printedIcon; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
