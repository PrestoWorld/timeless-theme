<?php

namespace Timeless\Blocks;

use function esc_attr;
use function esc_html;
use function esc_url;
use function get_author_posts_url;
use function get_current_user_id;
use function get_post_meta;
use function get_users;
use function get_userdata;
use function get_user_meta;
use function get_the_author_meta;
use function sanitize_html_class;
use function sprintf;
use function wp_get_avatar;
use function wp_kses_post;
use function wp_get_attachment_image_url;

class AuthorBoxBlock extends AbstractBlock
{
    protected string $blockId = 'jankx/author-box';

    protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
    {
        $authorId   = (int) ($attributes['authorId'] ?? 0);
        $showAvatar = $attributes['showAvatar'] ?? true;
        $avatarSize = (int) ($attributes['avatarSize'] ?? 80);
        $showBio    = $attributes['showBio'] ?? true;
        $showSocial = $attributes['showSocial'] ?? true;
        $showPosts  = $attributes['showPosts'] ?? false;
        $postsCount = (int) ($attributes['postsCount'] ?? 3);
        $layout     = $attributes['layout'] ?? 'horizontal';

        if ($authorId === 0) {
            $postAuthor = (int) get_the_author_meta('ID');
            if ($postAuthor) {
                $authorId = $postAuthor;
            } else {
                $currentUser = get_current_user_id();
                if ($currentUser) {
                    $authorId = $currentUser;
                }
            }
        }

        if (!$authorId) {
            $users = get_users(['role' => 'administrator', 'number' => 1]);
            if (!empty($users)) {
                $authorId = (int) $users[0]->ID;
            } else {
                return '<p>' . esc_html__('No author found', 'timeless') . '</p>';
            }
        }

        $author = get_userdata($authorId);
        if (!$author) {
            return '<p>' . esc_html__('Author not found', 'timeless') . '</p>';
        }

        $classes = ['wp-block-jankx-author-box', 'layout-' . sanitize_html_class($layout)];

        $wrapperAttrs = get_block_wrapper_attributes(['class' => implode(' ', $classes)]);

        ob_start();
        ?>
        <div <?php echo $wrapperAttrs; ?>>
            <?php if ($showAvatar) : ?>
                <div class="author-avatar">
                    <?php echo esc_html(get_avatar($authorId, $avatarSize, '', $author->display_name)); ?>
                </div>
            <?php endif; ?>

            <div class="author-info">
                <h3 class="author-name">
                    <a href="<?php echo esc_url(get_author_posts_url($authorId)); ?>">
                        <?php echo esc_html($author->display_name); ?>
                    </a>
                </h3>

                <?php if ($showBio && !empty($author->description)) : ?>
                    <div class="author-bio">
                        <?php echo wp_kses_post($author->description); ?>
                    </div>
                <?php endif; ?>

                <?php if ($showSocial) : ?>
                    <div class="author-social">
                        <?php echo $this->renderSocialLinks($authorId); ?>
                    </div>
                <?php endif; ?>

                <?php if ($showPosts) : ?>
                    <div class="author-posts">
                        <h4 class="posts-title"><?php esc_html_e('Recent Posts', 'timeless'); ?></h4>
                        <?php echo $this->renderRecentPosts($authorId, $postsCount); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function shouldRender(array $attributes): bool
    {
        return true;
    }

    private function renderSocialLinks(int $authorId): string
    {
        $socialLinks = [];

        $facebook   = get_user_meta($authorId, 'facebook', true);
        $twitter    = get_user_meta($authorId, 'twitter', true);
        $instagram  = get_user_meta($authorId, 'instagram', true);
        $linkedin   = get_user_meta($authorId, 'linkedin', true);
        $youtube    = get_user_meta($authorId, 'youtube', true);
        $website    = get_user_meta($authorId, 'user_url', true);

        if ($facebook) {
            $socialLinks[] = [
                'url'   => $facebook,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                'label' => 'Facebook',
            ];
        }
        if ($twitter) {
            $socialLinks[] = [
                'url'   => $twitter,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
                'label' => 'Twitter',
            ];
        }
        if ($instagram) {
            $socialLinks[] = [
                'url'   => $instagram,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.418-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.875 1.418 2.026 1.418 3.323s-.49 2.448-1.418 3.244c-.875.807-2.026 1.297-3.323 1.297zm7.718-1.297c-.875.807-2.026 1.297-3.323 1.297s-2.448-.49-3.323-1.297c-.928-.875-1.418-2.026-1.418-3.323s.49-2.448 1.418-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.875 1.418 2.026 1.418 3.323s-.49 2.448-1.418 3.244z"/></svg>',
                'label' => 'Instagram',
            ];
        }
        if ($linkedin) {
            $socialLinks[] = [
                'url'   => $linkedin,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
                'label' => 'LinkedIn',
            ];
        }
        if ($youtube) {
            $socialLinks[] = [
                'url'   => $youtube,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
                'label' => 'YouTube',
            ];
        }
        if ($website) {
            $socialLinks[] = [
                'url'   => $website,
                'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
                'label' => 'Website',
            ];
        }

        $output = '';
        foreach ($socialLinks as $link) {
            $output .= sprintf(
                '<a href="%s" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a>',
                esc_url($link['url']),
                esc_attr($link['label']),
                $link['icon']
            );
        }

        return $output;
    }

    private function renderRecentPosts(int $authorId, int $postsCount): string
    {
        $posts = get_posts([
            'author'         => $authorId,
            'numberposts'    => $postsCount,
            'post_status'    => 'publish',
        ]);

        if (empty($posts)) {
            return '<p>' . esc_html__('No posts found', 'timeless') . '</p>';
        }

        $output = '<ul class="posts-list">';
        foreach ($posts as $post) {
            $output .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url(get_permalink($post->ID)),
                esc_html($post->post_title)
            );
        }
        $output .= '</ul>';

        return $output;
    }
}
