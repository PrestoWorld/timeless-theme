<?php

namespace Timeless\Blocks;

use function apply_filters;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function register_rest_route;
use function rest_ensure_response;

class ModalBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/modal';

    public function boot(): void
    {
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script(
                'micromodal',
                'https://unpkg.com/micromodal@0.4.10/dist/micromodal.min.js',
                [],
                '0.4.10',
                false
            );
        });

        add_action('rest_api_init', [$this, 'registerRestEndpoints']);
    }

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $modalId = $attributes['modalId'] ?? ('modal-' . uniqid());
        $triggerType = $attributes['triggerType'] ?? 'button';
        $triggerText = $attributes['triggerText'] ?? __('Open Modal', 'timeless');
        $triggerUrl = $attributes['triggerUrl'] ?? '';
        $triggerTarget = $attributes['triggerTarget'] ?? '_self';
        $customSelector = $attributes['customSelector'] ?? '';
        $modalSize = $attributes['modalSize'] ?? 'medium';
        $customWidth = $attributes['customWidth'] ?? '';
        $customWidthUnit = $attributes['customWidthUnit'] ?? 'px';
        $closeOnOverlayClick = $attributes['closeOnOverlayClick'] ?? true;
        $closeOnEscape = $attributes['closeOnEscape'] ?? true;
        $showCloseButton = $attributes['showCloseButton'] ?? true;
        $animationType = $attributes['animationType'] ?? 'fade';
        $animationDuration = (int) ($attributes['animationDuration'] ?? 300);
        $backdropColor = $attributes['backdropColor'] ?? 'rgba(0,0,0,0.5)';
        $backdropBlur = $attributes['backdropBlur'] ?? '';
        $zIndex = (int) ($attributes['zIndex'] ?? 1000);
        $disableScroll = $attributes['disableScroll'] ?? false;
        $disableFocus = $attributes['disableFocus'] ?? false;

        $triggerAttrs = [
            'class' => 'jankx-modal-trigger',
            'data-modal-id' => esc_attr($modalId),
            'data-trigger-type' => esc_attr($triggerType),
        ];

        if ($triggerType === 'button') {
            $triggerHtml = sprintf(
                '<button type="button"%s>%s</button>',
                $this->attributesToHtml($triggerAttrs),
                esc_html($triggerText)
            );
        } elseif ($triggerType === 'link') {
            $triggerHtml = sprintf(
                '<a href="%s"%s target="%s">%s</a>',
                esc_url($triggerUrl ?: '#'),
                $this->attributesToHtml($triggerAttrs),
                esc_attr($triggerTarget),
                esc_html($triggerText)
            );
        } else {
            $triggerHtml = sprintf(
                '<span%s data-custom-selector="%s">%s</span>',
                $this->attributesToHtml($triggerAttrs),
                esc_attr($customSelector),
                esc_html($triggerText)
            );
        }

        $modalClasses = ['jankx-modal', 'jankx-modal--' . esc_attr($modalSize)];
        if ($customWidth !== '') {
            $modalClasses[] = 'jankx-modal--custom-width';
        }

        $modalStyle = '';
        if ($customWidth !== '') {
            $modalStyle .= 'max-width:' . esc_attr($customWidth . $customWidthUnit) . ';';
        }
        if ($backdropBlur !== '') {
            $modalStyle .= '--modal-backdrop-blur:' . esc_attr($backdropBlur) . ';';
        }

        $closeBtn = '';
        if ($showCloseButton) {
            $closeBtn = sprintf(
                '<button class="jankx-modal-close" data-modal-id="%s" aria-label="%s">×</button>',
                esc_attr($modalId),
                esc_attr__('Close modal', 'timeless')
            );
        }

        ob_start();
        ?>
        <?php echo $triggerHtml; ?>

        <div class="jankx-modal-overlay" data-modal-id="<?php echo esc_attr($modalId); ?>" style="z-index:<?php echo $zIndex; ?>;"></div>

        <div class="<?php echo esc_attr(implode(' ', $modalClasses)); ?>" id="<?php echo esc_attr($modalId); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modalId); ?>-title" style="<?php echo esc_attr($modalStyle); ?>">
            <div class="jankx-modal-content">
                <?php echo $closeBtn; ?>
                <div class="jankx-modal-body">
                    <?php echo wp_kses_post($content); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    public function registerRestEndpoints(): void
    {
        register_rest_route('timeless/v1', '/modal/open', [
            'methods' => 'POST',
            'callback' => function () {
                return rest_ensure_response(['status' => 'ok']);
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    private function attributesToHtml(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $key => $value) {
            $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }
        return $html;
    }
}
