<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function get_block_wrapper_attributes;
use function in_array;
use function implode;
use function sanitize_html_class;
use function sprintf;
use function wp_kses_post;

class SocialSharingBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/social-sharing';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $networks = $attributes['networks'] ?? [];
        $iconSize = $attributes['iconSize'] ?? 'medium';
        $showLabels = $attributes['showLabels'] ?? true;
        $alignment = $attributes['alignment'] ?? 'center';
        $showHeading = $attributes['showHeading'] ?? false;
        $headingText = $attributes['headingText'] ?? __('Share this post', 'timeless');

        if (!is_array($networks) || empty($networks)) {
            return '';
        }

        $classes = array_filter([
            'jankx-social-sharing',
            'social-sharing--size-' . sanitize_html_class($iconSize),
            'social-sharing--align-' . sanitize_html_class($alignment),
        ]);

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => implode(' ', $classes),
        ]);

        ob_start();
        ?>
        <div <?php echo $wrapperAttributes; ?>>
            <?php if ($showHeading && $headingText !== '') : ?>
                <h3 class="social-sharing-heading"><?php echo esc_html($headingText); ?></h3>
            <?php endif; ?>

            <div class="social-sharing-networks">
                <?php foreach ($networks as $network) : ?>
                    <?php
                    $networkClasses = array_filter([
                        'social-sharing-network',
                        'network-' . sanitize_html_class($network),
                    ]);
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $networkClasses)); ?>" data-network="<?php echo esc_attr($network); ?>">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }
}
