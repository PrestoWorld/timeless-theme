<?php

namespace Timeless\Blocks;

use function absint;
use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_url;
use function get_the_ID;
use function is_numeric;
use function max;
use function min;
use function number_format_i18n;
use function sprintf;
use function str_repeat;
use function trim;

class StarRatingBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/star-rating';

    const DEFAULT_SVG_FULL  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    const DEFAULT_SVG_HALF  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4V6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>';
    const DEFAULT_SVG_EMPTY = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $attributes = [
            'displayStyle'   => $attributes['displayStyle'] ?? 'stars',
            'ratingSource'   => $attributes['ratingSource'] ?? 'manual',
            'manualRating'   => absint($attributes['manualRating'] ?? 5),
            'starSize'       => absint($attributes['starSize'] ?? 16),
            'starColor'      => $attributes['starColor'] ?? '#f1c40f',
            'starEmptyColor' => $attributes['starEmptyColor'] ?? '#dddddd',
            'showCount'      => !empty($attributes['showCount']),
            'countMetaKey'   => $attributes['countMetaKey'] ?? 'timeless_rating_count',
            'align'          => $attributes['align'] ?? 'left',
            'position'       => $attributes['position'] ?? '',
            'top'            => $attributes['top'] ?? '',
            'right'          => $attributes['right'] ?? '',
            'bottom'         => $attributes['bottom'] ?? '',
            'left'           => $attributes['left'] ?? '',
            'zIndex'         => $attributes['zIndex'] ?? '',
            'iconType'       => $attributes['iconType'] ?? 'text',
            'svgFull'        => $attributes['svgFull'] ?? '',
            'svgHalf'        => $attributes['svgHalf'] ?? '',
            'svgEmpty'       => $attributes['svgEmpty'] ?? '',
            'className'      => $attributes['className'] ?? '',
        ];

        $postId   = get_the_ID() ?: 0;
        $rating   = (float) $attributes['manualRating'];
        $count    = 0;

        $rating = (float) apply_filters('timeless/star_rating/value', $rating, $attributes, $postId);
        $count  = (int)   apply_filters('timeless/star_rating/count', $count,  $attributes, $postId);

        $rating = max(0.0, min(5.0, $rating));

        $style = sprintf(
            '--star-size: %dpx; --star-color: %s; --star-empty-color: %s; text-align: %s;',
            $attributes['starSize'],
            esc_attr($attributes['starColor']),
            esc_attr($attributes['starEmptyColor']),
            esc_attr($attributes['align'])
        );

        if (!empty($attributes['position'])) {
            $style .= sprintf(' position: %s;', esc_attr($attributes['position']));

            foreach (['top', 'right', 'bottom', 'left'] as $prop) {
                if (isset($attributes[$prop]) && $attributes[$prop] !== '') {
                    $val = trim((string) $attributes[$prop]);
                    if (is_numeric($val)) {
                        $val .= 'px';
                    }
                    $style .= sprintf(' %s: %s;', $prop, esc_attr($val));
                }
            }
        }

        if (isset($attributes['zIndex']) && $attributes['zIndex'] !== '') {
            $style .= sprintf(' z-index: %s;', esc_attr($attributes['zIndex']));
        }

        $wrapper_classes = ['wp-block-jankx-star-rating'];
        if (!empty($attributes['className'])) {
            $wrapper_classes[] = $attributes['className'];
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" style="<?php echo esc_attr($style); ?>">
            <div class="jankx-star-rating">
                <?php if ($attributes['displayStyle'] === 'summary'): ?>
                    <span class="jankx-rating-summary">
                        <span class="jankx-rating-summary__icon">★</span>
                        <span class="jankx-rating-summary__score"><?php echo esc_html(number_format_i18n($rating, 1)); ?></span>
                        <?php if ($attributes['showCount'] && $count > 0): ?>
                            <span class="jankx-rating-summary__count">(<?php echo number_format_i18n($count); ?>)</span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <div class="jankx-stars" title="<?php echo esc_attr(sprintf(__('Rated %s out of 5', 'timeless'), $rating)); ?>">
                        <?php echo $this->renderStars($rating, $attributes); ?>
                    </div>
                    <?php if ($attributes['showCount'] && $count > 0): ?>
                        <span class="jankx-rating-count">
                            (<?php echo number_format_i18n($count); ?>)
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderStars(float $rating, array $attributes): string
    {
        $output        = '';
        $full_stars    = (int) floor($rating);
        $has_half_star = ($rating - $full_stars) >= 0.5;
        $iconType      = $attributes['iconType'] ?? 'text';

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full_stars) {
                $output .= $this->getStarIcon('full', $iconType, $attributes);
            } elseif ($i === $full_stars + 1 && $has_half_star) {
                $output .= $this->getStarIcon('half', $iconType, $attributes);
            } else {
                $output .= $this->getStarIcon('empty', $iconType, $attributes);
            }
        }

        return $output;
    }

    protected function getStarIcon(string $type, string $iconType, array $attributes): string
    {
        $classes = "jankx-star {$type}";

        if ($iconType === 'svg') {
            $svgContent = '';
            if ($type === 'full')  $svgContent = !empty($attributes['svgFull'])  ? $attributes['svgFull']  : self::DEFAULT_SVG_FULL;
            if ($type === 'half')  $svgContent = !empty($attributes['svgHalf'])  ? $attributes['svgHalf']  : self::DEFAULT_SVG_HALF;
            if ($type === 'empty') $svgContent = !empty($attributes['svgEmpty']) ? $attributes['svgEmpty'] : self::DEFAULT_SVG_EMPTY;

            return sprintf('<span class="%s is-svg">%s</span>', $classes, $svgContent);
        }

        $symbol = ($type === 'full' || $type === 'half') ? '★' : '☆';
        return sprintf('<span class="%s">%s</span>', $classes, $symbol);
    }
}
