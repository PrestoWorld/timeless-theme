<?php
/**
 * Advanced Button Block
 *
 * Server-side render for jankx/advanced-button block.
 * Mirrors the React save.tsx output for SSR compatibility.
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function preg_match;
use function sprintf;
use function wp_kses_post;

/**
 * Class AdvancedButton
 */
class AdvancedButtonBlock extends AbstractBlock
{
    /**
     * Block ID
     */
    protected string $blockId = 'jankx/advanced-button';

    /**
     * Render the advanced button block.
     *
     * @param array  $attributes Block attributes
     * @param string $content    Inner block content
     * @param \WP_Block|null $block Block instance
     * @return string HTML
     */
    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $attributes = $this->getAttributesWithDefaults($attributes);

        $triggerType   = $attributes['triggerType'] ?? 'link';
        $buttonType    = $attributes['buttonType'] ?? 'button';
        $text          = $attributes['text'] ?? '';
        $url           = $attributes['url'] ?? '#';
        $title         = $attributes['title'] ?? '';
        $linkTarget    = $attributes['linkTarget'] ?? '';
        $rel           = $attributes['rel'] ?? '';
        $conditionType = $attributes['conditionType'] ?? 'always';
        $showForPostType = $attributes['showForPostType'] ?? '';
        $hoverAni      = $attributes['hoverAnimation'] ?? 'none';
        $unhoverAni    = $attributes['unhoverAnimation'] ?? 'none';
        $modalId       = $attributes['modalId'] ?? '';
        $iconPosition  = $attributes['iconPosition'] ?? 'left';
        $showLabel     = $attributes['showLabel'] ?? true;
        $renderIconOutside = $attributes['renderIconOutside'] ?? false;
        $className     = $attributes['className'] ?? '';

        // Respect post type condition
        if ($conditionType === 'post-type' && $showForPostType !== '') {
            $currentPostType = null;
            if ($block instanceof \WP_Block) {
                $ctx = is_array($block->context ?? null) ? $block->context : [];
                $currentPostType = $ctx['postType'] ?? ($ctx['previewPostType'] ?? null);
            }
            if (!$currentPostType) {
                $postId = get_the_ID();
                if ($postId) {
                    $currentPostType = get_post_type($postId);
                } elseif (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof \WP_Post) {
                    $currentPostType = $GLOBALS['post']->post_type;
                }
            }
            if ($currentPostType !== null && $currentPostType !== $showForPostType) {
                return '';
            }
        }

        // Fallback: extract data attributes from saved content when attributes are defaults
        if (($conditionType === 'always' || $showForPostType === '') && is_string($content) && $content !== '') {
            if (preg_match('/data-condition-type="([^"]*)"/', $content, $m)) {
                $conditionType = $m[1] ?: $conditionType;
            }
            if (preg_match('/data-show-for-post-type="([^"]*)"/', $content, $m2)) {
                $showForPostType = $m2[1] ?: $showForPostType;
            }
        }

        // Build wrapper classes
        $wrapperClasses = $this->getBlockClass('jankx-advanced-button');
        if (!empty($attributes['useIconBlocks'])) {
            $wrapperClasses .= ' icon-position-' . esc_attr($iconPosition);
        }

        // Build link/button classes
        $linkClasses = 'jankx-advanced-button__link';

        // Color classes
        $bgColor = $attributes['backgroundColor'] ?? '';
        $textColor = $attributes['textColor'] ?? '';
        $gradient = $attributes['gradient'] ?? '';

        if ($bgColor && empty($attributes['is-style-outline'])) {
            $linkClasses .= ' has-' . esc_attr($bgColor) . '-background-color';
        }
        if ($textColor) {
            $linkClasses .= ' has-' . esc_attr($textColor) . '-color';
        }
        if ($gradient) {
            $linkClasses .= ' has-' . esc_attr($gradient) . '-gradient-background';
        }

        // Style variants
        if (!empty($attributes['is-style-outline'])) {
            $linkClasses .= ' is-style-outline';
        }
        if (!empty($attributes['is-style-text-link'])) {
            $linkClasses .= ' is-style-text-link';
        }

        // Icon position
        if ($iconPosition !== 'left') {
            $linkClasses .= ' icon-position-' . esc_attr($iconPosition);
        }

        // Animation classes
        if ($hoverAni !== 'none') {
            $linkClasses .= ' hover-ani-' . esc_attr($hoverAni);
        }
        if ($unhoverAni !== 'none') {
            $linkClasses .= ' unhover-ani-' . esc_attr($unhoverAni);
        }

        // Modal trigger
        if ($triggerType === 'modal') {
            $linkClasses .= ' jankx-button-modal-trigger';
        }

        // Default colors flag
        $hasNoColors = empty($bgColor) && empty($textColor) && empty($gradient);
        if ($hasNoColors) {
            $linkClasses .= ' is-default-colors';
        }

        if ($className !== '') {
            $linkClasses .= ' ' . esc_attr($className);
        }

        // Build inline styles
        $style = $this->buildStyles($attributes);

        // Build data attributes
        $dataAttrs = '';
        $dataAttrs .= sprintf(' data-condition-type="%s"', esc_attr($conditionType));
        if ($showForPostType !== '') {
            $dataAttrs .= sprintf(' data-show-for-post-type="%s"', esc_attr($showForPostType));
        }
        $dataAttrs .= sprintf(' data-trigger-type="%s"', esc_attr($triggerType));
        if ($hoverAni !== 'none') {
            $dataAttrs .= sprintf(' data-hover-ani="%s"', esc_attr($hoverAni));
        }
        if ($unhoverAni !== 'none') {
            $dataAttrs .= sprintf(' data-unhover-ani="%s"', esc_attr($unhoverAni));
        }

        // Modal data attributes
        if ($triggerType === 'modal') {
            $dataAttrs .= sprintf(' data-modal-id="%s"', esc_attr($modalId));
            $dataAttrs .= ' data-micromodal-trigger=""';
            if (!empty($attributes['modalShareObjectId'])) {
                $dataAttrs .= ' data-share-object-id="true"';
            }
            if (!empty($attributes['modalSharePostTitle'])) {
                $dataAttrs .= ' data-share-post-title="true"';
            }
            if (!empty($attributes['modalShareCurrentUrl'])) {
                $dataAttrs .= ' data-share-current-url="true"';
            }
        }

        // Build inner content
        $innerContent = $this->renderInnerContent($attributes, $content, $renderIconOutside);

        // Build the trigger element
        $triggerHtml = $this->renderTriggerElement(
            $triggerType,
            $buttonType,
            $linkClasses,
            $style,
            $url,
            $linkTarget,
            $rel,
            $title,
            $dataAttrs,
            $innerContent
        );

        // Wrap with icon outside if needed
        if ($renderIconOutside) {
            $iconMarkup = $this->renderIconWrapper($attributes);
            $iconPositionLeftTop = in_array($iconPosition, ['left', 'top'], true);

            return sprintf(
                '<div class="%s">%s%s%s</div>',
                esc_attr($wrapperClasses),
                ($iconPositionLeftTop ? $iconMarkup : ''),
                $triggerHtml,
                (!$iconPositionLeftTop ? $iconMarkup : '')
            );
        }

        return sprintf(
            '<div class="%s">%s</div>',
            esc_attr($wrapperClasses),
            $triggerHtml
        );
    }

    /**
     * Build inline CSS styles from attributes.
     */
    private function buildStyles(array $attributes): string
    {
        $styles = [];

        $border = $attributes['style']['border'] ?? [];
        if (isset($border['radius'])) {
            $radius = $border['radius'];
            if (is_array($radius)) {
                $tl = $radius['topLeft'] ?? 0;
                $tr = $radius['topRight'] ?? 0;
                $br = $radius['bottomRight'] ?? 0;
                $bl = $radius['bottomLeft'] ?? 0;
                $styles[] = sprintf('border-radius:%s %s %s %s;', esc_attr($tl), esc_attr($tr), esc_attr($br), esc_attr($bl));
            } else {
                $styles[] = sprintf('border-radius:%s;', esc_attr($radius));
            }
        }

        if (isset($border['color'])) {
            $styles[] = sprintf('border-color:%s;', esc_attr($border['color']));
        }
        if (isset($border['width'])) {
            $styles[] = sprintf('border-width:%s;', esc_attr($border['width']));
        }
        if (isset($border['style'])) {
            $styles[] = sprintf('border-style:%s;', esc_attr($border['style']));
        }

        $styleColor = $attributes['style']['color'] ?? [];
        if (isset($styleColor['text'])) {
            $styles[] = sprintf('color:%s;', esc_attr($styleColor['text']));
        }
        if (isset($styleColor['gradient'])) {
            $styles[] = sprintf('background:%s;', esc_attr($styleColor['gradient']));
        } elseif (isset($styleColor['background'])) {
            $styles[] = sprintf('background-color:%s;', esc_attr($styleColor['background']));
        }

        if (!empty($attributes['is-style-outline'])) {
            $styles[] = 'background-color:transparent;';
            $styles[] = 'background-image:none;';
            if (isset($styleColor['text'])) {
                $styles[] = sprintf('border-color:%s;', esc_attr($styleColor['text']));
            }
        }

        if (!empty($attributes['is-style-text-link'])) {
            $styles[] = 'background-color:transparent;';
            $styles[] = 'background-image:none;';
            $styles[] = 'border:none;';
            $styles[] = 'padding:0;';
        }

        return implode('', $styles);
    }

    /**
     * Render inner content (icon wrapper + text).
     */
    private function renderInnerContent(array $attributes, string $content, bool $renderIconOutside): string
    {
        $showLabel = $attributes['showLabel'] ?? true;
        $text = $attributes['text'] ?? '';

        $html = '';

        if (!$renderIconOutside) {
            $html .= sprintf('<span class="button-icon-wrapper">%s</span>', wp_kses_post($content));
        }

        if ($showLabel) {
            $sanitizedText = preg_replace('/<\/?a[^>]*>/i', '', $text);
            $html .= sprintf('<span class="button-text">%s</span>', esc_html($sanitizedText));
        }

        return $html;
    }

    /**
     * Render icon wrapper when renderIconOutside is true.
     */
    private function renderIconWrapper(array $attributes): string
    {
        return sprintf('<span class="button-icon-wrapper">%s</span>', '');
    }

    /**
     * Render the trigger element (<a> or <button>) based on trigger type.
     */
    private function renderTriggerElement(
        string $triggerType,
        string $buttonType,
        string $classes,
        string $style,
        string $url,
        string $linkTarget,
        string $rel,
        string $title,
        string $dataAttrs,
        string $innerContent
    ): string {
        $baseAttrs = sprintf(
            ' class="%s" style="%s" title="%s"%s',
            esc_attr($classes),
            esc_attr($style),
            esc_attr($title),
            $dataAttrs
        );

        switch ($triggerType) {
            case 'link':
                return sprintf(
                    '<a href="%s"%s%s%s>%s</a>',
                    esc_url($url),
                    $linkTarget !== '' ? sprintf(' target="%s"', esc_attr($linkTarget)) : '',
                    $rel !== '' ? sprintf(' rel="%s"', esc_attr($rel)) : '',
                    $baseAttrs,
                    $innerContent
                );

            case 'button':
                return sprintf(
                    '<button type="%s"%s>%s</button>',
                    esc_attr($buttonType),
                    $baseAttrs,
                    $innerContent
                );

            case 'detail-link':
                return sprintf(
                    '<a href="#"%s>%s</a>',
                    $baseAttrs,
                    $innerContent
                );

            case 'modal':
                return sprintf(
                    '<button type="button"%s>%s</button>',
                    $baseAttrs,
                    $innerContent
                );

            default:
                return sprintf(
                    '<button type="button"%s>%s</button>',
                    $baseAttrs,
                    $innerContent
                );
        }
    }

    /**
     * Get block attributes with defaults applied.
     */
    public function getAttributesWithDefaults(array $attributes = []): array
    {
        $blockJson = $this->blockPath . '/block.json';
        if (!file_exists($blockJson)) {
            return $attributes;
        }

        $json = file_get_contents($blockJson);
        if ($json === false) {
            return $attributes;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['attributes'])) {
            return $attributes;
        }

        $merged = [];
        foreach ($data['attributes'] as $attrName => $attrDef) {
            $default = $attrDef['default'] ?? null;
            $merged[$attrName] = $attributes[$attrName] ?? $default;
        }

        return array_merge($merged, $attributes);
    }
}
