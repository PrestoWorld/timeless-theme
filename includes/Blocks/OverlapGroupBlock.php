<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function implode;
use function in_array;
use function is_array;
use function sanitize_html_class;
use function sprintf;

class OverlapGroupBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/overlap-group';

    private array $allowedTags = ['div', 'section', 'article', 'aside', 'main'];

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $tag = 'div';
        if (!empty($attributes['tagName']) && in_array($attributes['tagName'], $this->allowedTags, true)) {
            $tag = $attributes['tagName'];
        }

        $classes = ['jankx-overlap-group'];
        $styles = [];

        $positionType = $attributes['positionType'] ?? 'relative';
        if (!in_array($positionType, ['static', 'relative', 'absolute'], true)) {
            $positionType = 'relative';
        }

        if ($positionType !== 'static') {
            $styles[] = 'position:' . $positionType;
            $classes[] = 'jankx-overlap-group--' . sanitize_html_class($positionType);
        }

        $offsetUnit = $attributes['offsetUnit'] ?? 'px';
        if (!in_array($offsetUnit, ['px', '%', 'rem', 'vw', 'vh'], true)) {
            $offsetUnit = 'px';
        }

        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (isset($attributes[$side]) && $attributes[$side] !== '' && $attributes[$side] !== null) {
                $styles[] = $side . ':' . (float) $attributes[$side] . $offsetUnit;
            }
        }

        if (isset($attributes['zIndex']) && $attributes['zIndex'] !== '' && $attributes['zIndex'] !== null) {
            $styles[] = 'z-index:' . (int) $attributes['zIndex'];
        }

        if (!empty($attributes['width'])) {
            $styles[] = 'width:' . esc_attr($attributes['width']);
        }

        if (!empty($attributes['maxWidth'])) {
            $styles[] = 'max-width:' . esc_attr($attributes['maxWidth']);
        }

        $pullUp = (int) ($attributes['pullUp'] ?? 0);
        if ($pullUp > 0) {
            $styles[] = 'margin-top:-' . $pullUp . 'px';
        }

        $wrapperAttributes = [
            'class' => implode(' ', $classes),
            'style' => implode(';', $styles),
        ];

        return sprintf('<%1$s %2$s>%3$s</%1$s>', $tag, $this->attributesToHtml($wrapperAttributes), $content);
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function attributesToHtml(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $key => $value) {
            if ($value !== '' && $value !== null) {
                $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
            }
        }
        return $html;
    }
}
