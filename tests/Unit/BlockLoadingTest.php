<?php

declare(strict_types=1);

namespace Timeless\Tests\Unit;

use Timeless\Blocks\AbstractBlock;
use Timeless\Blocks\BlockRendererInterface;
use Timeless\Blocks\BlockRegistry;

class BlockLoadingTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        spl_autoload_register(function (string $class): void {
            $prefix = 'Timeless\\Blocks\\';
            if (str_starts_with($class, $prefix)) {
                $relativeClass = substr($class, strlen($prefix));
                $file = get_template_directory() . '/includes/Blocks/' . $relativeClass . '.php';
                if (is_file($file) && is_readable($file)) {
                    require_once $file;
                }
            }
        });
    }
    private const BLOCKS_DIR = __DIR__ . '/../../includes/Blocks';
    private const EXPECTED_BLOCKS = [
        'jankx/star-rating'         => 'StarRatingBlock',
        'jankx/sticky-box'          => 'StickyBoxBlock',
        'jankx/svg-icon'            => 'SvgIconBlock',
        'jankx/table-of-content'    => 'TableOfContentBlock',
        'jankx/term-list'           => 'TermListBlock',
        'jankx/term-post-count'     => 'TermPostCountBlock',
        'jankx/testimonial'         => 'TestimonialBlock',
        'jankx/testimonials'        => 'TestimonialsBlock',
        'jankx/text-input'          => 'TextInputBlock',
        'jankx/typography'          => 'TypographyBlock',
        'jankx/user-menu'           => 'UserMenuBlock',
        'jankx/wordpress'           => 'WordPressBlock',
        'jankx/wrapper'             => 'WrapperBlock',
    ];

    /**
     * @dataProvider blockClassProvider
     */
    public function testBlockClassExists(string $expectedClass, string $blockId): void
    {
        $this->assertTrue(class_exists($expectedClass), "Class {$expectedClass} does not exist.");
    }

    /**
     * @dataProvider blockClassProvider
     */
    public function testBlockExtendsAbstractBlock(string $expectedClass): void
    {
        $reflection = new \ReflectionClass($expectedClass);
        $this->assertTrue(
            $reflection->isSubclassOf(AbstractBlock::class),
            "{$expectedClass} does not extend AbstractBlock."
        );
    }

    /**
     * @dataProvider blockClassProvider
     */
    public function testBlockImplementsRendererInterface(string $expectedClass): void
    {
        $reflection = new \ReflectionClass($expectedClass);
        $this->assertTrue(
            $reflection->implementsInterface(BlockRendererInterface::class),
            "{$expectedClass} does not implement BlockRendererInterface."
        );
    }

    /**
     * @dataProvider blockClassProvider
     */
    public function testBlockHasCorrectBlockId(string $expectedClass, string $blockId): void
    {
        $reflection = new \ReflectionClass($expectedClass);
        $property = $reflection->getProperty('blockId');
        $property->setAccessible(true);

        // Create instance without calling constructor (which may trigger WP hooks)
        $instance = $reflection->newInstanceWithoutConstructor();
        $actualBlockId = $property->getValue($instance);

        $this->assertSame($blockId, $actualBlockId, "Block ID mismatch for {$expectedClass}.");
    }

    /**
     * @dataProvider blockClassProvider
     */
    public function testBlockHasRenderBlockMethod(string $expectedClass): void
    {
        $reflection = new \ReflectionClass($expectedClass);
        $this->assertTrue(
            $reflection->hasMethod('renderBlock'),
            "{$expectedClass} does not override renderBlock()."
        );
        $method = $reflection->getMethod('renderBlock');
        $this->assertTrue($method->isProtected(), "{$expectedClass}::renderBlock() must be protected.");
    }

    public static function blockClassProvider(): array
    {
        $data = [];
        foreach (self::EXPECTED_BLOCKS as $blockId => $className) {
            $data[$className] = ['Timeless\\Blocks\\' . $className, $blockId];
        }
        return $data;
    }

    public function testAutoloaderLoadsAllBlocks(): void
    {
        // Register the same autoloader as functions.php
        spl_autoload_register(function (string $class): void {
            $prefix = 'Timeless\\Blocks\\';
            if (str_starts_with($class, $prefix)) {
                $relativeClass = substr($class, strlen($prefix));
                $file = get_template_directory() . '/includes/Blocks/' . $relativeClass . '.php';
                if (is_file($file) && is_readable($file)) {
                    require_once $file;
                }
            }
        });

        foreach (self::EXPECTED_BLOCKS as $blockId => $className) {
            $fullClass = 'Timeless\\Blocks\\' . $className;
            $this->assertTrue(
                class_exists($fullClass),
                "Autoloader failed to load {$fullClass}."
            );
        }
    }
}
