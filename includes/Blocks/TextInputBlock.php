<?php

namespace Timeless\Blocks;

use function absint;
use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function preg_match;
use function sprintf;

class TextInputBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/text-input';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $label = $attributes['label'] ?? '';
        $placeholder = $attributes['placeholder'] ?? '';
        $inputType = $attributes['inputType'] ?? 'text';
        $required = !empty($attributes['required']);
        $disabled = !empty($attributes['disabled']);
        $inputName = $attributes['inputName'] ?? '';
        $inputValue = $attributes['inputValue'] ?? '';
        $width = $attributes['width'] ?? '100%';
        $borderRadius = absint($attributes['borderRadius'] ?? 4);
        $iconPosition = $attributes['iconPosition'] ?? 'left';

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => sprintf('jankx-text-input-wrapper jankx-text-input-wrapper--has-icon jankx-text-input-wrapper--icon-%s', esc_attr($iconPosition)),
            'style' => sprintf('width: %s; position: relative;', esc_attr($width)),
        ]);

        $inputAttrs = [
            'type' => esc_attr($inputType),
            'placeholder' => esc_attr($placeholder),
            'class' => 'jankx-text-input filter-input-' . esc_attr($attributes['filterType'] ?? 'keyword') . ' filter-input-keyword',
            'style' => sprintf(
                'border-radius: %dpx; width: 100%%; padding-%s: 40px; min-height: 40px; min-width: 200px; border: 1px solid #ccc; outline: none;',
                $borderRadius,
                $iconPosition === 'left' ? 'left' : 'right'
            ),
        ];

        if ($inputName) {
            $inputAttrs['name'] = esc_attr($inputName);
        }

        if ($inputValue) {
            $inputAttrs['value'] = esc_attr($inputValue);
        }

        if ($required) {
            $inputAttrs['required'] = 'required';
        }

        if ($disabled) {
            $inputAttrs['disabled'] = 'disabled';
        }

        $inputAttrString = '';
        foreach ($inputAttrs as $key => $value) {
            $inputAttrString .= sprintf('%s="%s" ', $key, $value);
        }

        ob_start();
        ?>
        <div <?php echo $wrapperAttrs; ?>>
            <?php if ($label) : ?>
                <label class="jankx-text-input-label"><?php echo esc_html($label); ?></label>
            <?php endif; ?>
            <div class="jankx-text-input-container" style="position: relative; display: flex; align-items: center; width: 100%;">
                <div class="jankx-text-input-icon-container" style="position: absolute; <?php echo $iconPosition; ?>: 15px; z-index: 1; pointer-events: none; display: flex; align-items: center; justify-content: center; width: 20px; height: 20px; overflow: hidden;">
                    <style>.jankx-text-input-icon-container svg { width: 100%; height: 100%; }</style>
                    <?php echo $content; ?>
                </div>
                <input <?php echo $inputAttrString; ?>/>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
