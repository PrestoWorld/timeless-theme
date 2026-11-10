<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function is_array;
use function sprintf;
use function wp_kses_post;

class DynamicDataTemplateBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/dynamic-data-template';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $wrapperClasses = ['dynamic-data-template'];
        if (!empty($attributes['className'])) {
            $wrapperClasses[] = esc_attr($attributes['className']);
        }

        // Overlay icon classes
        if (!empty($attributes['overlayIcon'])) {
            $wrapperClasses[] = 'has-overlay-icon';
            $wrapperClasses[] = 'overlay-icon-position-' . esc_attr($attributes['overlayIconPosition'] ?? 'center');
            $wrapperClasses[] = 'overlay-icon-mode-' . esc_attr($attributes['overlayIconShowMode'] ?? 'always-show');
            $wrapperClasses[] = 'overlay-icon-target-' . esc_attr($attributes['overlayIconTarget'] ?? 'featured-image');
        }

        if (!empty($attributes['enableOverlay'])) {
            $wrapperClasses[] = 'has-overlay';
        }
        if (!empty($attributes['overlayLinkToPost'])) {
            $wrapperClasses[] = 'overlay-link-to-post';
        }

        $wrapperAttributes = [
            'class' => implode(' ', $wrapperClasses),
            'data-overlay-icon' => esc_attr($attributes['overlayIcon'] ?? ''),
            'data-overlay-icon-type' => esc_attr($attributes['overlayIconType'] ?? 'class'),
            'data-overlay-icon-image' => esc_attr($attributes['overlayIconImageUrl'] ?? ''),
            'data-overlay-icon-text' => esc_attr($attributes['overlayIconText'] ?? ''),
            'data-overlay-icon-rotate' => esc_attr((int) ($attributes['overlayIconRotate'] ?? 0)),
            'data-overlay-icon-position' => esc_attr($attributes['overlayIconPosition'] ?? 'center'),
            'data-overlay-icon-size' => esc_attr((int) ($attributes['overlayIconSize'] ?? 24)),
            'data-overlay-icon-color' => esc_attr($attributes['overlayIconColor'] ?? '#ffffff'),
            'data-overlay-icon-bg' => esc_attr($attributes['overlayIconBackground'] ?? 'rgba(0,0,0,0.5)'),
            'data-overlay-icon-mode' => esc_attr($attributes['overlayIconShowMode'] ?? 'always-show'),
            'data-overlay-icon-target' => esc_attr($attributes['overlayIconTarget'] ?? 'featured-image'),
            'data-item-bg-type' => esc_attr($attributes['itemBgType'] ?? 'none'),
            'data-item-bg-color' => esc_attr($attributes['itemBgColor'] ?? ''),
            'data-item-bg-image-url' => esc_attr($attributes['itemBgImageUrl'] ?? ''),
            'data-item-bg-image-source' => esc_attr($attributes['itemBgImageSource'] ?? 'custom'),
            'data-item-bg-position' => esc_attr($attributes['itemBgPosition'] ?? 'center center'),
            'data-item-bg-size' => esc_attr($attributes['itemBgSize'] ?? 'cover'),
            'data-item-bg-repeat' => esc_attr($attributes['itemBgRepeat'] ?? 'no-repeat'),
            'data-item-bg-overlay' => esc_attr($attributes['itemBgOverlay'] ?? ''),
            'data-item-bg-content-align' => esc_attr($attributes['itemBgContentAlign'] ?? 'bottom'),
            'data-enable-overlay' => !empty($attributes['enableOverlay']) ? 'true' : 'false',
            'data-overlay-gradient' => esc_attr($attributes['overlayGradient'] ?? ''),
            'data-overlay-link-to-post' => !empty($attributes['overlayLinkToPost']) ? 'true' : 'false',
        ];

        $wrapperAttrsString = '';
        foreach ($wrapperAttributes as $key => $value) {
            if ($value !== '' && $value !== null) {
                $wrapperAttrsString .= sprintf(' %s="%s"', $key, $value);
            }
        }

        ob_start();
        ?>
        <div class="<?php echo implode(' ', $wrapperClasses); ?>"<?php echo $wrapperAttrsString; ?>>
            <?php echo wp_kses_post($content); ?>
            <?php echo $this->renderOverlay($attributes, $block); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    protected function renderOverlay(array $attributes, ?\WP_Block $block = null): string
    {
        if (empty($attributes['enableOverlay'])) {
            return '';
        }

        $gradient = $attributes['overlayGradient'] ?? 'linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%)';
        $linkToPost = !empty($attributes['overlayLinkToPost']);

        $overlayStyle = sprintf(
            'background:%s;position:absolute;top:0;left:0;right:0;bottom:0;z-index:1;',
            esc_attr($gradient)
        );

        $overlayHtml = sprintf(
            '<div class="dynamic-data-template__overlay" style="%s"></div>',
            esc_attr($overlayStyle)
        );

        if ($linkToPost) {
            $permalink = '';
            global $post;
            if ($post instanceof \WP_Post) {
                $permalink = get_permalink($post->ID);
            }

            if (!empty($permalink)) {
                $overlayHtml = sprintf(
                    '<a href="%s" class="dynamic-data-template__overlay dynamic-data-template__overlay-link" style="%s" aria-label="%s"></a>',
                    esc_url($permalink),
                    esc_attr($overlayStyle),
                    esc_attr__('View details', 'timeless')
                );
            }
        }

        return $overlayHtml;
    }
}
