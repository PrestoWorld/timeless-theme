<?php
/**
 * Block Registry
 *
 * Singleton registry that auto-discovers Gutenberg blocks from
 * resources/blocks/ and manages their registration with WordPress.
 *
 * Design patterns:
 *   - Singleton: single registry instance
 *   - Registry: maps block names to block instances
 *   - Lazy Loading: blocks instantiated only when accessed
 *   - Factory: creates AbstractBlock subclasses from conventions
 *
 * Performance optimizations:
 *   - Block discovery cached statically (no repeated filesystem scans)
 *   - Block classes lazy-loaded (not all blocks instantiated at once)
 *   - Assets only enqueued when block is actually rendered on page
 *   - register_block_type called once per block
 *
 * @package Timeless\Blocks
 */

declare(strict_types=1);

namespace Timeless\Blocks;

use function apply_filters;
use function class_exists;
use function glob;
use function is_file;
use function is_readable;
use function str_starts_with;
use function ucwords;

/**
 * Class BlockRegistry
 */
class BlockRegistry
{
    /**
     * Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Path to blocks directory
     */
    private string $blocksDir;

    /**
     * Discovered blocks: name => block directory path
     * Populated once via discover()
     */
    private array $discoveredBlocks = [];

    /**
     * Instantiated block objects: name => AbstractBlock
     * Lazy loaded
     */
    private array $blocks = [];

    /**
     * Whether registration has been performed
     */
    private bool $registered = false;

    /**
     * BlockRegistry constructor (private - use getInstance())
     */
    private function __construct(string $blocksDir)
    {
        $this->blocksDir = rtrim($blocksDir, '/');
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(string $blocksDir = ''): self
    {
        if (self::$instance === null) {
            if ($blocksDir === '') {
                $blocksDir = get_template_directory() . '/resources/blocks';
            }
            self::$instance = new self($blocksDir);
        }
        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Auto-discover all blocks by scanning resources/blocks for block.json.
     * Results are cached after first scan.
     */
    public function discover(): array
    {
        if (!empty($this->discoveredBlocks)) {
            return $this->discoveredBlocks;
        }

        $pattern = $this->blocksDir . '/*/block.json';
        $files = glob($pattern, GLOB_NOSORT);

        foreach ($files as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }

            $blockDir = dirname($file);
            $blockName = $this->extractBlockName($file);
            if ($blockName !== '') {
                $this->discoveredBlocks[$blockName] = $blockDir;
            }
        }

        $this->discoveredBlocks = apply_filters('timeless_discovered_blocks', $this->discoveredBlocks, $this);

        return $this->discoveredBlocks;
    }

    /**
     * Extract block name from block.json name field.
     */
    private function extractBlockName(string $blockJson): string
    {
        $json = file_get_contents($blockJson);
        if ($json === false) {
            return '';
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['name'])) {
            return '';
        }

        return (string) $data['name'];
    }

    /**
     * Get all discovered block names.
     */
    public function getBlockNames(): array
    {
        return array_keys($this->discover());
    }

    /**
     * Get a block instance by name. Lazy instantiates if not already loaded.
     */
    public function getBlock(string $name): ?AbstractBlock
    {
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
        }

        $this->discover();

        $blockDir = $this->discoveredBlocks[$name] ?? null;
        if ($blockDir === null) {
            return null;
        }

        $blockClass = $this->resolveBlockClass($name);

        if ($blockClass !== AbstractBlock::class && class_exists($blockClass)) {
            $this->blocks[$name] = new $blockClass($name, $blockDir);
        } else {
            $this->blocks[$name] = new class($name, $blockDir) extends AbstractBlock {
                public function render(array $attributes, string $content): string
                {
                    return $content;
                }
            };
        }

        return $this->blocks[$name];
    }

    /**
     * Resolve the PHP class name for a block based on directory naming convention.
     */
    private function resolveBlockClass(string $name): string
    {
        $parts = explode('/', $name, 2);
        $shortName = $parts[count($parts) - 1] ?? $name;

        $className = str_replace('-', ' ', $shortName);
        $className = ucwords($className);
        $className = str_replace(' ', '', $className);

        return 'Timeless\\Blocks\\' . $className;
    }

    /**
     * Register all discovered blocks with WordPress.
     * Called once on init.
     */
    public function registerAll(): void
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->getBlockNames() as $name) {
            $block = $this->getBlock($name);
            if ($block !== null) {
                $block->register();
            }
        }

        $this->registered = true;

        add_filter('render_block', [$this, 'enqueueBlockAssets'], 10, 2);
    }

    /**
     * Conditionally enqueue block assets when block is rendered.
     */
    public function enqueueBlockAssets(string $content, array $block): string
    {
        $blockName = $block['blockName'] ?? '';
        if ($blockName === '') {
            return $content;
        }

        $blockInstance = $this->getBlock($blockName);
        if ($blockInstance === null) {
            return $content;
        }

        $assets = $blockInstance->getFrontendAssets();
        foreach ($assets as $handle) {
            if (str_starts_with($handle, '-view')) {
                wp_enqueue_script($handle);
            } elseif (str_starts_with($handle, '-style')) {
                wp_enqueue_style($handle);
            }
        }

        return $content;
    }

    /**
     * Check if a block is registered/discovered.
     */
    public function hasBlock(string $name): bool
    {
        $this->discover();
        return isset($this->discoveredBlocks[$name]);
    }

    /**
     * Get all instantiated blocks (only those that have been accessed).
     */
    public function getLoadedBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Clear cache (useful for tests)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
