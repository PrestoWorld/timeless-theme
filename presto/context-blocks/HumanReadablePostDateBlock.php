<?php

declare(strict_types=1);

namespace PrestoWorld\Theme\Timeless\ContextBlocks;

use PrestoWorld\Modules\ContextBuilder\BaseBlock;

/**
 * timeless/human-readable-post-date — displays the post date in a
 * human-readable format (e.g. "3 hours ago").
 */
class HumanReadablePostDateBlock extends BaseBlock
{
    protected string $name = 'timeless/human-readable-post-date';

    protected array $attributes = [
        'showIcon' => ['type' => 'boolean', 'default' => true],
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
        $showIcon = $attributes['showIcon'] ?? true;
        $icon = $showIcon ? '<svg class="icon-clock" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ' : '';

        return sprintf(
            '<time class="pw-human-readable-date" datetime="%s">%s%s</time>',
            htmlspecialchars($attributes['date'] ?? date('c'), ENT_QUOTES, 'UTF-8'),
            $icon,
            htmlspecialchars($attributes['text'] ?? 'Just now', ENT_QUOTES, 'UTF-8'),
        );
    }
}
