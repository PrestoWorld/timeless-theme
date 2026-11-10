<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function is_array;
use function sprintf;

class DynamicDataSortRulesBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/dynamic-data-sort-rules';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $enabled = $attributes['enabled'] ?? false;
        if (!$enabled) {
            return '';
        }

        $rules = $attributes['rules'] ?? [];
        if (!is_array($rules) || empty($rules)) {
            return '';
        }

        $displayLabel = $attributes['displayLabel'] ?? __('Sắp xếp theo', 'timeless');

        ob_start();
        ?>
        <div class="jankx-dynamic-data-sort-dropdown" style="display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-bottom:20px;">
            <?php if ($displayLabel !== '') : ?>
                <label class="sort-label"><?php echo esc_html($displayLabel); ?></label>
            <?php endif; ?>
            <select class="sort-select jankx-data-sorter">
                <?php foreach ($rules as $index => $rule) : ?>
                    <?php
                    $label = !empty($rule['label']) ? $rule['label'] : sprintf(__('Option %d', 'timeless'), $index + 1);
                    $value = esc_attr(wp_json_encode([
                        'orderBy'  => $rule['orderBy'] ?? 'date',
                        'order'    => $rule['order'] ?? 'DESC',
                        'metaKey'  => $rule['metaKey'] ?? '',
                        'metaType' => $rule['metaType'] ?? '',
                    ]));
                    ?>
                    <option value="<?php echo $index; ?>" data-rule="<?php echo $value; ?>">
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
