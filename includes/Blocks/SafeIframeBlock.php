<?php

namespace Timeless\Blocks;

use function absint;
use function esc_attr;
use function esc_html;
use function esc_url;
use function filter_var;
use function FILTER_VALIDATE_URL;
use function implode;
use function is_array;
use function sprintf;
use function wp_kses_post;

class SafeIframeBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/safe-iframe';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $url = $attributes['url'] ?? '';

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return sprintf(
                '<div class="safe-iframe-block safe-iframe-error"><p>%s</p></div>',
                esc_html__('Invalid iframe URL', 'timeless')
            );
        }

        $title = $attributes['title'] ?? 'Embedded Content';
        $width = $attributes['width'] ?? '100%';
        $height = $attributes['height'] ?? '500px';
        $aspectRatio = $attributes['aspectRatio'] ?? '';
        $useAspectRatio = $attributes['useAspectRatio'] ?? false;
        $allowFullscreen = $attributes['allowFullscreen'] ?? true;
        $loading = $attributes['loading'] ?? 'lazy';
        $sandbox = $attributes['sandbox'] ?? ['allow-scripts', 'allow-same-origin'];
        $allow = $attributes['allow'] ?? '';
        $borderRadius = (int) ($attributes['borderRadius'] ?? 0);
        $showBorder = $attributes['showBorder'] ?? false;
        $borderWidth = (int) ($attributes['borderWidth'] ?? 1);
        $borderColor = $attributes['borderColor'] ?? '#ddd';
        $showShadow = $attributes['showShadow'] ?? false;
        $customCSS = $attributes['customCSS'] ?? '';

        $iframeStyles = [];
        if ($useAspectRatio) {
            $iframeStyles[] = 'width:100%';
            $iframeStyles[] = 'height:100%';
        } else {
            $iframeStyles[] = 'width:' . esc_attr($width);
            $iframeStyles[] = 'height:' . esc_attr($height);
        }

        if ($showBorder) {
            $iframeStyles[] = sprintf('border:%dpx solid %s', absint($borderWidth), esc_attr($borderColor));
        } else {
            $iframeStyles[] = 'border:none';
        }

        if ($borderRadius > 0) {
            $iframeStyles[] = sprintf('border-radius:%dpx', absint($borderRadius));
        }

        if ($showShadow) {
            $iframeStyles[] = 'box-shadow:0 4px 6px rgba(0,0,0,0.1)';
        }

        $containerStyles = [];
        $containerClasses = ['safe-iframe-block'];

        if ($useAspectRatio && $aspectRatio !== '') {
            $containerStyles[] = 'position:relative';
            $containerStyles[] = 'width:100%';
            $containerStyles[] = sprintf('padding-bottom:calc(100%% / (%s))', esc_attr($aspectRatio));
            $containerClasses[] = 'safe-iframe-block--aspect-ratio';
        }

        $iframeAttrs = [
            'src' => esc_url($url),
            'title' => esc_attr($title),
            'style' => implode(';', $iframeStyles),
            'loading' => esc_attr($loading),
        ];

        if (!empty($sandbox) && is_array($sandbox)) {
            $iframeAttrs['sandbox'] = esc_attr(implode(' ', $sandbox));
        }

        if ($allow !== '') {
            $iframeAttrs['allow'] = esc_attr($allow);
        }

        if ($allowFullscreen) {
            $iframeAttrs['allowfullscreen'] = 'allowfullscreen';
        }

        $iframeHtml = '<iframe';
        foreach ($iframeAttrs as $key => $value) {
            if ($key === 'allowfullscreen') {
                $iframeHtml .= ' ' . $key;
            } else {
                $iframeHtml .= sprintf(' %s="%s"', $key, $value);
            }
        }
        $iframeHtml .= '></iframe>';

        if ($useAspectRatio && $aspectRatio !== '') {
            $innerContainerStyles = 'position:absolute;top:0;left:0;width:100%;height:100%;';
            $iframeHtml = sprintf(
                '<div style="%s">%s</div>',
                esc_attr($innerContainerStyles),
                $iframeHtml
            );
        }

        $wrapperAttrs = [
            'class' => implode(' ', $containerClasses),
        ];
        if (!empty($containerStyles)) {
            $wrapperAttrs['style'] = implode(';', $containerStyles);
        }

        $customCSSHtml = '';
        if ($customCSS !== '') {
            $customCSSHtml = sprintf('<style>%s</style>', wp_kses_post($customCSS));
        }

        return sprintf(
            '<div %s>%s%s</div>',
            $this->attributesToHtml($wrapperAttrs),
            $iframeHtml,
            $customCSSHtml
        );
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
