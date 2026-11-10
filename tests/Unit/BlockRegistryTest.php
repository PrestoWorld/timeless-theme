<?php

declare(strict_types=1);

namespace Timeless\Tests\Unit;

use Timeless\Blocks\BlockRegistry;

class BlockRegistryTest extends \PHPUnit\Framework\TestCase
{
    private const EXPECTED_BLOCK_IDS = [
        'jankx/star-rating',
        'jankx/sticky-box',
        'jankx/svg-icon',
        'jankx/table-of-content',
        'jankx/term-list',
        'jankx/term-post-count',
        'jankx/testimonial',
        'jankx/testimonials',
        'jankx/text-input',
        'jankx/typography',
        'jankx/user-menu',
        'jankx/wordpress',
        'jankx/wrapper',
    ];

    protected function setUp(): void
    {
        BlockRegistry::reset();
    }

    protected function tearDown(): void
    {
        BlockRegistry::reset();
    }

    public function testRegistryDiscoversAllBlocks(): void
    {
        $registry = BlockRegistry::getInstance(__DIR__ . '/../../resources/blocks');
        $discovered = $registry->discover();

        foreach (self::EXPECTED_BLOCK_IDS as $blockId) {
            $this->assertArrayHasKey(
                $blockId,
                $discovered,
                "Registry did not discover block {$blockId}."
            );
        }
    }

    public function testRegistryGetBlockIds(): void
    {
        $registry = BlockRegistry::getInstance(__DIR__ . '/../../resources/blocks');
        $blockIds = $registry->getBlockIds();

        foreach (self::EXPECTED_BLOCK_IDS as $blockId) {
            $this->assertContains($blockId, $blockIds, "Block ID {$blockId} not in registry block IDs.");
        }
    }

    /**
     * @dataProvider blockIdProvider
     */
    public function testRegistryHasBlock(string $blockId): void
    {
        $registry = BlockRegistry::getInstance(__DIR__ . '/../../resources/blocks');
        $this->assertTrue($registry->hasBlock($blockId), "Registry::hasBlock({$blockId}) returned false.");
    }

    public function testRegistryReturnsNullForUnknownBlock(): void
    {
        $registry = BlockRegistry::getInstance(__DIR__ . '/../../resources/blocks');
        $block = $registry->getBlock('jankx/non-existent-block');
        $this->assertNull($block, "Registry should return null for unknown block.");
    }

    public static function blockIdProvider(): array
    {
        $data = [];
        foreach (self::EXPECTED_BLOCK_IDS as $blockId) {
            $data[$blockId] = [$blockId];
        }
        return $data;
    }
}
