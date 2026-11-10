<?php

namespace Timeless\Blocks;

use function apply_filters;
use function class_exists;
use function glob;
use function is_file;
use function is_readable;
use function str_starts_with;
use function ucwords;

class BlockRegistry
{
    private static ?self $instance = null;

    private string $blocksDir;

    private array $discoveredBlocks = [];

    private array $blocks = [];

    private bool $registered = false;

    private function __construct(string $blocksDir)
    {
        $this->blocksDir = rtrim($blocksDir, '/');
    }

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

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }

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
            $blockId = $this->extractBlockId($file);
            if ($blockId !== '') {
                $this->discoveredBlocks[$blockId] = $blockDir;
            }
        }

        $this->discoveredBlocks = apply_filters('timeless_discovered_blocks', $this->discoveredBlocks, $this);

        return $this->discoveredBlocks;
    }

    private function extractBlockId(string $blockJson): string
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

    public function getBlockIds(): array
    {
        return array_keys($this->discover());
    }

    public function getBlock(string $blockId): ?AbstractBlock
    {
        if (isset($this->blocks[$blockId])) {
            return $this->blocks[$blockId];
        }

        $this->discover();

        $blockDir = $this->discoveredBlocks[$blockId] ?? null;
        if ($blockDir === null) {
            return null;
        }

        $blockClass = $this->resolveBlockClass($blockId);

        if ($blockClass !== AbstractBlock::class && class_exists($blockClass)) {
            $this->blocks[$blockId] = new $blockClass($blockId, $blockDir);
        } else {
            $this->blocks[$blockId] = new class($blockId, $blockDir) extends AbstractBlock {
                protected function renderBlock(array $attributes, string $content, ?\WP_Block $block): string
                {
                    return $content;
                }
            };
        }

        return $this->blocks[$blockId];
    }

    private function resolveBlockClass(string $blockId): string
    {
        $parts = explode('/', $blockId, 2);
        $shortName = $parts[count($parts) - 1] ?? $blockId;

        $pascal = str_replace('-', ' ', $shortName);
        $pascal = ucwords($pascal);
        $pascal = str_replace(' ', '', $pascal);

        $candidates = [
            'Timeless\\Blocks\\' . $pascal . 'Block',
            'Timeless\\Blocks\\' . $pascal,
            AbstractBlock::class,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== AbstractBlock::class && class_exists($candidate)) {
                return $candidate;
            }
        }

        return AbstractBlock::class;
    }

    public function registerAll(): void
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->getBlockIds() as $blockId) {
            $block = $this->getBlock($blockId);
            if ($block !== null) {
                $block->boot();
                $block->register();
            }
        }

        $this->registered = true;

        add_filter('render_block', [$this, 'enqueueBlockAssets'], 10, 2);
    }

    public function enqueueBlockAssets(string $content, array $block): string
    {
        $blockId = $block['blockName'] ?? '';
        if ($blockId === '') {
            return $content;
        }

        $blockInstance = $this->getBlock($blockId);
        if ($blockInstance === null) {
            return $content;
        }

        return $content;
    }

    public function hasBlock(string $blockId): bool
    {
        $this->discover();
        return isset($this->discoveredBlocks[$blockId]);
    }

    public function getLoadedBlocks(): array
    {
        return $this->blocks;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
