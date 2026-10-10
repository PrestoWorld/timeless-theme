<?php

declare(strict_types=1);

namespace PrestoWorld\Theme\Timeless\ContextBlocks;

use PrestoWorld\Modules\ContextBuilder\BaseBlock;

/**
 * timeless/dynamic-data-template — defines the template for each item in a
 * dynamic data layout. It contains inner blocks that build the post card.
 */
class DynamicDataTemplateBlock extends BaseBlock
{
    protected string $name = 'timeless/dynamic-data-template';

    protected array $attributes = [
        'templateLayout' => ['type' => 'string', 'default' => 'default'],
        'className' => ['type' => 'string', 'default' => ''],
        'thumbnailPosition' => ['type' => 'string', 'default' => 'top'],
        'itemSpacing' => ['type' => 'string', 'default' => 'normal'],
        'backgroundColor' => ['type' => 'string', 'default' => ''],
        'textColor' => ['type' => 'string', 'default' => ''],
    ];

    protected string $renderMode = 'ssr';

    public function __construct(?string $name = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
    }

    public function render(array $attributes, string $content = ''): string
    {
        $className = htmlspecialchars($attributes['className'] ?? '', ENT_QUOTES, 'UTF-8');
        $templateLayout = htmlspecialchars($attributes['templateLayout'] ?? 'default', ENT_QUOTES, 'UTF-8');

        $classes = ['wp-block-timeless-dynamic-data-template'];
        if ($className !== '') {
            $classes[] = $className;
        }

        return sprintf(
            '<div class="%s" data-template-layout="%s">%s</div>',
            implode(' ', $classes),
            $templateLayout,
            $content,
        );
    }
}
