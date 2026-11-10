<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function file_exists;
use function get_post_meta;
use function is_array;
use function sprintf;
use function wp_kses_post;

class AdvancedFilterBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/advanced-filter';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        if (!$this->shouldRender($attributes)) {
            return '';
        }

        $filterType    = $attributes['filterType'] ?? 'taxonomy';
        $label         = $attributes['label'] ?? '';
        $enabled       = $attributes['enabled'] ?? true;
        $taxonomy      = $attributes['taxonomy'] ?? '';
        $displayStyle  = $attributes['displayStyle'] ?? 'list';
        $listingType   = $attributes['listingType'] ?? 'terms';
        $showCount     = $attributes['showCount'] ?? false;
        $showEmpty     = $attributes['showEmptyTerms'] ?? false;
        $showTopLevel  = $attributes['showOnlyTopLevel'] ?? false;
        $showHierarchy = $attributes['showHierarchy'] ?? true;
        $multiSelect   = $attributes['multipleSelection'] ?? false;
        $showLabels    = $attributes['showLabels'] ?? true;
        $collapsible   = $attributes['collapsible'] ?? false;
        $defaultExpanded = $attributes['defaultExpanded'] ?? true;
        $metaKey       = $attributes['metaKey'] ?? '';
        $inputType     = $attributes['inputType'] ?? 'select';
        $placeholder   = $attributes['placeholder'] ?? '';
        $minValue      = $attributes['minValue'] ?? '';
        $maxValue      = $attributes['maxValue'] ?? '';
        $currency      = $attributes['currency'] ?? '';
        $dateField     = $attributes['dateField'] ?? '';
        $dateRange     = $attributes['dateRange'] ?? '';
        $showSearchBtn = $attributes['showSearchButton'] ?? false;
        $searchBtnText = $attributes['searchButtonText'] ?? __('Search', 'timeless');
        $searchBtnDisplay = $attributes['searchButtonDisplay'] ?? 'button';
        $searchBtnIcon = $attributes['searchButtonIcon'] ?? '';
        $keywordAction = $attributes['keywordAction'] ?? 'filter';
        $width         = $attributes['width'] ?? '';

        if (!$enabled) {
            return '';
        }

        $wrapperClasses = $this->getBlockClass('jankx-advanced-filter');
        $wrapperClasses .= ' filter-type-' . esc_attr($filterType);
        $wrapperClasses .= ' display-style-' . esc_attr($displayStyle);
        $wrapperClasses .= ' listing-type-' . esc_attr($listingType);
        if ($collapsible) {
            $wrapperClasses .= ' is-collapsible';
            if (!$defaultExpanded) {
                $wrapperClasses .= ' is-collapsed';
            }
        }
        if ($multiSelect) {
            $wrapperClasses .= ' is-multi-select';
        }
        if ($showCount) {
            $wrapperClasses .= ' shows-count';
        }
        if ($showEmpty) {
            $wrapperClasses .= ' shows-empty';
        }
        if ($showTopLevel) {
            $wrapperClasses .= ' shows-top-level';
        }
        if (!$showHierarchy) {
            $wrapperClasses .= ' no-hierarchy';
        }
        if ($width !== '') {
            $wrapperClasses .= ' custom-width';
        }

        $style = '';
        if ($width !== '') {
            $style .= sprintf('width:%s;', esc_attr($width));
        }

        $dataAttrs = '';
        $dataAttrs .= sprintf(' data-filter-type="%s"', esc_attr($filterType));
        $dataAttrs .= sprintf(' data-display-style="%s"', esc_attr($displayStyle));
        $dataAttrs .= sprintf(' data-listing-type="%s"', esc_attr($listingType));
        if ($taxonomy !== '') {
            $dataAttrs .= sprintf(' data-taxonomy="%s"', esc_attr($taxonomy));
        }
        if ($metaKey !== '') {
            $dataAttrs .= sprintf(' data-meta-key="%s"', esc_attr($metaKey));
        }
        if ($inputType !== '') {
            $dataAttrs .= sprintf(' data-input-type="%s"', esc_attr($inputType));
        }
        if ($currency !== '') {
            $dataAttrs .= sprintf(' data-currency="%s"', esc_attr($currency));
        }
        if ($dateField !== '') {
            $dataAttrs .= sprintf(' data-date-field="%s"', esc_attr($dateField));
        }

        $labelHtml = '';
        if ($showLabels && $label !== '') {
            $labelHtml = sprintf('<label class="jankx-advanced-filter__label">%s</label>', esc_html($label));
        }

        $filterContent = $this->renderFilterContent($attributes, $filterType, $taxonomy, $inputType, $placeholder, $minValue, $maxValue, $showSearchBtn, $searchBtnText, $searchBtnDisplay, $searchBtnIcon, $keywordAction);

        return sprintf(
            '<div class="%s" style="%s"%s>%s%s</div>',
            esc_attr($wrapperClasses),
            esc_attr($style),
            $dataAttrs,
            $labelHtml,
            $filterContent
        );
    }

    protected function shouldRender(array $attributes): bool
    {
        return !empty($attributes['enabled']);
    }

    private function renderFilterContent(array $attributes, string $filterType, string $taxonomy, string $inputType, string $placeholder, string $minValue, string $maxValue, bool $showSearchBtn, string $searchBtnText, string $searchBtnDisplay, string $searchBtnIcon, string $keywordAction): string
    {
        switch ($filterType) {
            case 'taxonomy':
                return $this->renderTaxonomyFilter($taxonomy, $inputType, $placeholder, $showSearchBtn, $searchBtnText, $searchBtnDisplay, $searchBtnIcon);
            case 'meta':
                return $this->renderMetaFilter($attributes, $inputType, $placeholder, $minValue, $maxValue, $currency, $showSearchBtn, $searchBtnText, $searchBtnDisplay, $searchBtnIcon);
            case 'date':
                return $this->renderDateFilter($dateField ?? '', $dateRange ?? '', $showSearchBtn, $searchBtnText, $searchBtnDisplay, $searchBtnIcon);
            case 'keyword':
                return $this->renderKeywordFilter($placeholder, $keywordAction, $searchBtnText, $searchBtnDisplay, $searchBtnIcon);
            default:
                return '';
        }
    }

    private function renderTaxonomyFilter(string $taxonomy, string $inputType, string $placeholder, bool $showSearchBtn, string $searchBtnText, string $searchBtnDisplay, string $searchBtnIcon): string
    {
        $terms = [];
        if ($taxonomy !== '' && taxonomy_exists($taxonomy)) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);
        }

        $optionsHtml = '';
        if (is_array($terms)) {
            foreach ($terms as $term) {
                $optionsHtml .= sprintf(
                    '<option value="%s">%s</option>',
                    esc_attr($term->slug),
                    esc_html($term->name)
                );
            }
        }

        $inputHtml = '';
        if ($inputType === 'select') {
            $inputHtml = sprintf(
                '<select class="jankx-advanced-filter__input jankx-advanced-filter__input--select"%s>%s</select>',
                $placeholder !== '' ? sprintf(' placeholder="%s"', esc_attr($placeholder)) : '',
                $optionsHtml
            );
        } elseif ($inputType === 'checkbox' || $inputType === 'radio') {
            foreach ($terms as $term) {
                $type = esc_attr($inputType);
                $inputHtml .= sprintf(
                    '<label class="jankx-advanced-filter__option"><input type="%s" name="%s[]" value="%s"> %s</label> ',
                    $type,
                    esc_attr($taxonomy),
                    esc_attr($term->slug ?? ''),
                    esc_html($term->name ?? '')
                );
            }
        }

        $searchHtml = '';
        if ($showSearchBtn) {
            $btnClass = $searchBtnDisplay === 'link' ? 'jankx-advanced-filter__btn--link' : 'jankx-advanced-filter__btn';
            $iconHtml = $searchBtnIcon !== '' ? sprintf('<span class="%s"></span> ', esc_attr($searchBtnIcon)) : '';
            $searchHtml = sprintf(
                '<button class="%s">%s%s</button>',
                esc_attr($btnClass),
                $iconHtml,
                esc_html($searchBtnText)
            );
        }

        return sprintf(
            '<div class="jankx-advanced-filter__content jankx-advanced-filter__content--taxonomy">%s%s</div>',
            $inputHtml,
            $searchHtml
        );
    }

    private function renderMetaFilter(array $attributes, string $inputType, string $placeholder, string $minValue, string $maxValue, string $currency, bool $showSearchBtn, string $searchBtnText, string $searchBtnDisplay, string $searchBtnIcon): string
    {
        $metaKey = $attributes['metaKey'] ?? '';

        $inputHtml = '';
        if ($inputType === 'select') {
            $inputHtml = sprintf(
                '<select class="jankx-advanced-filter__input jankx-advanced-filter__input--select"%s></select>',
                $placeholder !== '' ? sprintf(' placeholder="%s"', esc_attr($placeholder)) : ''
            );
        } elseif ($inputType === 'range') {
            $minAttr = $minValue !== '' ? sprintf(' min="%s"', esc_attr($minValue)) : '';
            $maxAttr = $maxValue !== '' ? sprintf(' max="%s"', esc_attr($maxValue)) : '';
            $inputHtml = sprintf(
                '<input type="range" class="jankx-advanced-filter__input jankx-advanced-filter__input--range"%s%s>',
                $minAttr,
                $maxAttr
            );
        } else {
            $inputHtml = sprintf(
                '<input type="text" class="jankx-advanced-filter__input jankx-advanced-filter__input--text"%s>',
                $placeholder !== '' ? sprintf(' placeholder="%s"', esc_attr($placeholder)) : ''
            );
        }

        $unitHtml = $currency !== '' ? sprintf('<span class="jankx-advanced-filter__unit">%s</span>', esc_html($currency)) : '';

        $searchHtml = '';
        if ($showSearchBtn) {
            $btnClass = $searchBtnDisplay === 'link' ? 'jankx-advanced-filter__btn--link' : 'jankx-advanced-filter__btn';
            $iconHtml = $searchBtnIcon !== '' ? sprintf('<span class="%s"></span> ', esc_attr($searchBtnIcon)) : '';
            $searchHtml = sprintf(
                '<button class="%s">%s%s</button>',
                esc_attr($btnClass),
                $iconHtml,
                esc_html($searchBtnText)
            );
        }

        return sprintf(
            '<div class="jankx-advanced-filter__content jankx-advanced-filter__content--meta">%s%s%s</div>',
            $inputHtml,
            $unitHtml,
            $searchHtml
        );
    }

    private function renderDateFilter(string $dateField, string $dateRange, bool $showSearchBtn, string $searchBtnText, string $searchBtnDisplay, string $searchBtnIcon): string
    {
        $inputHtml = sprintf(
            '<input type="date" class="jankx-advanced-filter__input jankx-advanced-filter__input--date" data-date-field="%s">',
            esc_attr($dateField)
        );

        $rangeHtml = '';
        if ($dateRange === 'range') {
            $rangeHtml = sprintf(
                '<input type="date" class="jankx-advanced-filter__input jankx-advanced-filter__input--date-end" data-date-field="%s">',
                esc_attr($dateField)
            );
        }

        $searchHtml = '';
        if ($showSearchBtn) {
            $btnClass = $searchBtnDisplay === 'link' ? 'jankx-advanced-filter__btn--link' : 'jankx-advanced-filter__btn';
            $iconHtml = $searchBtnIcon !== '' ? sprintf('<span class="%s"></span> ', esc_attr($searchBtnIcon)) : '';
            $searchHtml = sprintf(
                '<button class="%s">%s%s</button>',
                esc_attr($btnClass),
                $iconHtml,
                esc_html($searchBtnText)
            );
        }

        return sprintf(
            '<div class="jankx-advanced-filter__content jankx-advanced-filter__content--date">%s%s%s</div>',
            $inputHtml,
            $rangeHtml,
            $searchHtml
        );
    }

    private function renderKeywordFilter(string $placeholder, string $keywordAction, string $searchBtnText, string $searchBtnDisplay, string $searchBtnIcon): string
    {
        $inputHtml = sprintf(
            '<input type="text" class="jankx-advanced-filter__input jankx-advanced-filter__input--keyword"%s data-keyword-action="%s">',
            $placeholder !== '' ? sprintf(' placeholder="%s"', esc_attr($placeholder)) : '',
            esc_attr($keywordAction)
        );

        $searchHtml = '';
        if ($keywordAction === 'search') {
            $btnClass = $searchBtnDisplay === 'link' ? 'jankx-advanced-filter__btn--link' : 'jankx-advanced-filter__btn';
            $iconHtml = $searchBtnIcon !== '' ? sprintf('<span class="%s"></span> ', esc_attr($searchBtnIcon)) : '';
            $searchHtml = sprintf(
                '<button class="%s">%s%s</button>',
                esc_attr($btnClass),
                $iconHtml,
                esc_html($searchBtnText)
            );
        }

        return sprintf(
            '<div class="jankx-advanced-filter__content jankx-advanced-filter__content--keyword">%s%s</div>',
            $inputHtml,
            $searchHtml
        );
    }
}
