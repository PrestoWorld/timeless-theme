<?php

declare(strict_types=1);

namespace PrestoWorld\Theme\Timeless\ContextBlocks;

use PrestoWorld\Modules\ContextBuilder\BaseBlock;

/**
 * timeless/dynamic-data-layout — queries posts and renders them through a
 * template block. In CSR mode it outputs a placeholder for Bridge.js to fetch.
 */
class DynamicDataLayoutBlock extends BaseBlock
{
    protected string $name = 'timeless/dynamic-data-layout';

    protected array $attributes = [
        'queryPreset' => ['type' => 'string', 'default' => 'custom'],
        'postType' => ['type' => 'string', 'default' => 'post'],
        'postsPerPage' => ['type' => 'integer', 'default' => 10],
        'layout' => ['type' => 'string', 'default' => 'grid'],
        'columns' => ['type' => 'integer', 'default' => 3],
        'columnsTablet' => ['type' => 'integer', 'default' => 2],
        'columnsMobile' => ['type' => 'integer', 'default' => 1],
        'orderBy' => ['type' => 'string', 'default' => 'date'],
        'order' => ['type' => 'string', 'default' => 'DESC'],
        'taxQuery' => ['type' => 'array', 'default' => []],
        'metaQuery' => ['type' => 'array', 'default' => []],
        'keyword' => ['type' => 'string', 'default' => ''],
        'enablePagination' => ['type' => 'boolean', 'default' => false],
        'paginationStyle' => ['type' => 'string', 'default' => 'numbers'],
        'thumbnailPosition' => ['type' => 'string', 'default' => 'top'],
        'showExcerpt' => ['type' => 'boolean', 'default' => true],
        'showFeaturedImage' => ['type' => 'boolean', 'default' => true],
        'showDate' => ['type' => 'boolean', 'default' => true],
        'showAuthor' => ['type' => 'boolean', 'default' => false],
        'excerptLength' => ['type' => 'integer', 'default' => 55],
        'spaceBetween' => ['type' => 'integer', 'default' => 16],
    ];

    protected string $renderMode = 'csr'; // Query blocks default to CSR

    /**
     * The registry keys blocks by name; allow the theme to also expose the
     * legacy namespace while content is migrated.
     */
    public function __construct(?string $name = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
    }

    public function render(array $attributes, string $content = ''): string
    {
        $postType = htmlspecialchars($attributes['postType'] ?? 'post', ENT_QUOTES, 'UTF-8');
        $perPage = (int) ($attributes['postsPerPage'] ?? 10);
        $layout = htmlspecialchars($attributes['layout'] ?? 'grid', ENT_QUOTES, 'UTF-8');
        $columns = (int) ($attributes['columns'] ?? 3);

        return sprintf(
            '<div class="wp-block-timeless-dynamic-data-layout" ' .
            'data-post-type="%s" data-per-page="%d" data-layout="%s" data-columns="%d" ' .
            'data-order-by="%s" data-order="%s"></div>',
            $postType,
            $perPage,
            $layout,
            $columns,
            htmlspecialchars($attributes['orderBy'] ?? 'date', ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($attributes['order'] ?? 'DESC', ENT_QUOTES, 'UTF-8'),
        );
    }
}
