<?php

declare(strict_types=1);

namespace PrestoWorld\Theme\Timeless\Blocks;

use PrestoWorld\Modules\Gutenberg\Renderer\Blocks\AbstractBlock;
use PrestoWorld\Modules\Gutenberg\Renderer\Support\PostData;

/**
 * timeless/dynamic-data-template — renders its inner blocks once per post,
 * like core/post-template.
 */
class DynamicDataTemplateBlock extends AbstractBlock
{
    public function render(array $context): string
    {
        $posts = $context['posts'] ?? [];
        $inner = '';

        foreach ($posts as $post) {
            $postId   = (int) PostData::id($post);
            $postType = PostData::type($post);

            $postContent = '';
            foreach ($this->innerBlocks as $innerBlock) {
                $postContent .= $innerBlock->render(array_merge($context, [
                    'post'     => $post,
                    'postId'   => $postId,
                    'postType' => $postType,
                ]));
            }
            $inner .= $postContent;
        }

        $classes   = array_merge(['wp-block-timeless-dynamic-data-template'], $this->classes);
        $classAttr = !empty($classes) ? ' class="' . implode(' ', array_unique($classes)) . '"' : '';
        $styleAttr = !empty($this->styles) ? ' style="' . implode(';', $this->styles) . '"' : '';

        return "<div{$classAttr}{$styleAttr}>{$inner}</div>";
    }
}
