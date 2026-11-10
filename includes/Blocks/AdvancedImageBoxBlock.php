<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function file_exists;
use function get_the_post_thumbnail_url;
use function is_array;
use function sprintf;
use function wp_get_attachment_image_src;
use function wp_get_attachment_metadata;
use function wp_get_attachment_url;

class AdvancedImageBoxBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/advanced-image-box';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if (!$this->shouldRender($attributes)) {
            return '';
        }

        $url                  = $attributes['url'] ?? '';
        $alt                  = $attributes['alt'] ?? '';
        $title                = $attributes['title'] ?? '';
        $id                   = $attributes['id'] ?? 0;
        $width                = $attributes['width'] ?? '';
        $height               = $attributes['height'] ?? '';
        $aspectRatio          = $attributes['aspectRatio'] ?? '';
        $scale                = $attributes['scale'] ?? 100;
        $sizeSlug             = $attributes['sizeSlug'] ?? 'full';
        $href                 = $attributes['href'] ?? '';
        $linkTarget           = $attributes['linkTarget'] ?? '';
        $rel                  = $attributes['rel'] ?? '';
        $caption              = $attributes['caption'] ?? '';
        $showOverlayOnHover   = $attributes['showOverlayOnHover'] ?? false;
        $overlayAnimation     = $attributes['overlayAnimation'] ?? 'fade';
        $overlayAnimationDuration = $attributes['overlayAnimationDuration'] ?? 300;
        $overlayAnimationDelay    = $attributes['overlayAnimationDelay'] ?? 0;
        $overlayPosition      = $attributes['overlayPosition'] ?? 'center';
        $overlayBackground    = $attributes['overlayBackground'] ?? '';
        $overlayOpacity       = $attributes['overlayOpacity'] ?? 0.5;
        $imageHoverEffect     = $attributes['imageHoverEffect'] ?? 'none';
        $borderRadius         = $attributes['borderRadius'] ?? '';
        $preset               = $attributes['preset'] ?? '';
        $presetOptions        = $attributes['presetOptions'] ?? [];

        $wrapperClasses = $this->getBlockClass('jankx-advanced-image-box');
        $wrapperClasses .= ' preset-' . esc_attr($preset);
        if ($showOverlayOnHover) {
            $wrapperClasses .= ' has-overlay';
            $wrapperClasses .= ' overlay-anim-' . esc_attr($overlayAnimation);
            $wrapperClasses .= ' overlay-position-' . esc_attr($overlayPosition);
        }
        if ($imageHoverEffect !== 'none') {
            $wrapperClasses .= ' hover-effect-' . esc_attr($imageHoverEffect);
        }
        if ($borderRadius !== '') {
            $wrapperClasses .= ' has-border-radius';
        }

        $dataAttrs = '';
        $dataAttrs .= sprintf(' data-aspect-ratio="%s"', esc_attr($aspectRatio));
        $dataAttrs .= sprintf(' data-scale="%d"', (int) $scale);
        $dataAttrs .= sprintf(' data-overlay-animation="%s"', esc_attr($overlayAnimation));
        $dataAttrs .= sprintf(' data-overlay-duration="%d"', (int) $overlayAnimationDuration);
        $dataAttrs .= sprintf(' data-overlay-delay="%d"', (int) $overlayAnimationDelay);
        $dataAttrs .= sprintf(' data-overlay-position="%s"', esc_attr($overlayPosition));
        if ($overlayBackground !== '') {
            $dataAttrs .= sprintf(' data-overlay-background="%s"', esc_attr($overlayBackground));
        }
        $dataAttrs .= sprintf(' data-overlay-opacity="%s"', esc_attr($overlayOpacity));
        $dataAttrs .= sprintf(' data-hover-effect="%s"', esc_attr($imageHoverEffect));
        if ($borderRadius !== '') {
            $dataAttrs .= sprintf(' data-border-radius="%s"', esc_attr($borderRadius));
        }

        $style = '';
        if ($width !== '') {
            $style .= sprintf('width:%s;', esc_attr($width));
        }
        if ($height !== '') {
            $style .= sprintf('height:%s;', esc_attr($height));
        }
        if ($aspectRatio !== '') {
            $style .= sprintf('aspect-ratio:%s;', esc_attr($aspectRatio));
        }
        if ($scale !== 100) {
            $style .= sprintf('transform:scale(%s);', esc_attr($scale / 100));
        }

        $imageHtml = $this->renderImage($id, $url, $alt, $title, $sizeSlug, $width, $height);

        $overlayHtml = '';
        if ($showOverlayOnHover) {
            $overlayStyle = '';
            if ($overlayBackground !== '') {
                $overlayStyle .= sprintf('background-color:%s;', esc_attr($overlayBackground));
            }
            $overlayHtml = sprintf(
                '<div class="jankx-advanced-image-box__overlay" style="%s"></div>',
                esc_attr($overlayStyle)
            );
        }

        $captionHtml = '';
        if ($caption !== '') {
            $captionHtml = sprintf(
                '<figcaption class="jankx-advanced-image-box__caption">%s</figcaption>',
                esc_html($caption)
            );
        }

        $imageContent = sprintf(
            '<div class="jankx-advanced-image-box__image-wrapper"%s>%s%s</div>',
            $style !== '' ? sprintf(' style="%s"', esc_attr($style)) : '',
            $imageHtml,
            $overlayHtml
        );

        if ($href !== '') {
            $targetAttr = $linkTarget !== '' ? sprintf(' target="%s"', esc_attr($linkTarget)) : '';
            $relAttr = $rel !== '' ? sprintf(' rel="%s"', esc_attr($rel)) : '';
            $imageContent = sprintf(
                '<a href="%s"%s%s>%s</a>',
                esc_url($href),
                $targetAttr,
                $relAttr,
                $imageContent
            );
        }

        return sprintf(
            '<figure class="%s"%s>%s%s</figure>',
            esc_attr($wrapperClasses),
            $dataAttrs,
            $imageContent,
            $captionHtml
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function renderImage(int $id, string $url, string $alt, string $title, string $sizeSlug, string $width, string $height): string
    {
        $src = $url;
        $imgAlt = $alt;
        $imgTitle = $title;

        if ((int) $id > 0) {
            $imageSrc = wp_get_attachment_image_src((int) $id, $sizeSlug);
            if (is_array($imageSrc)) {
                $src = $imageSrc[0];
            }
            $meta = wp_get_attachment_metadata((int) $id);
            if ($imgAlt === '' && !empty($meta['image_meta']['title'])) {
                $imgAlt = $meta['image_meta']['title'];
            }
        }

        $attrs = sprintf('src="%s" alt="%s"', esc_url($src), esc_attr($imgAlt));
        if ($imgTitle !== '') {
            $attrs .= sprintf(' title="%s"', esc_attr($imgTitle));
        }
        if ($width !== '') {
            $attrs .= sprintf(' width="%s"', esc_attr($width));
        }
        if ($height !== '') {
            $attrs .= sprintf(' height="%s"', esc_attr($height));
        }

        return sprintf('<img %s>', $attrs);
    }
}
