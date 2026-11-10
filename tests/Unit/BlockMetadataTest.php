<?php

declare(strict_types=1);

namespace Timeless\Tests\Unit;

class BlockMetadataTest extends \PHPUnit\Framework\TestCase
{
    private const BLOCKS_DIR = __DIR__ . '/../../resources/blocks';

    private const EXPECTED_BLOCKS = [
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

    /**
     * @dataProvider blockJsonProvider
     */
    public function testBlockJsonExists(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';

        $this->assertFileExists($blockJsonPath, "Missing block.json for {$blockId} at {$blockJsonPath}.");
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testBlockJsonIsValid(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';

        $this->assertFileExists($blockJsonPath);

        $json = file_get_contents($blockJsonPath);
        $this->assertIsString($json, "Unable to read block.json for {$blockId}.");

        $data = json_decode($json, true);
        $this->assertIsArray($data, "block.json for {$blockId} is not valid JSON.");
        $this->assertSame(
            JSON_ERROR_NONE,
            json_last_error(),
            "JSON error for {$blockId}: " . json_last_error_msg()
        );
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testBlockJsonHasRequiredFields(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';

        $data = json_decode(file_get_contents($blockJsonPath), true);
        $this->assertIsArray($data);

        $this->assertArrayHasKey('name', $data, "block.json for {$blockId} missing 'name'.");
        $this->assertSame($blockId, $data['name'], "block.json name '{$data['name']}' does not match expected '{$blockId}'.");

        $this->assertArrayHasKey('title', $data, "block.json for {$blockId} missing 'title'.");
        $this->assertIsString($data['title'], "block.json title for {$blockId} is not a string.");
        $this->assertNotEmpty($data['title'], "block.json title for {$blockId} is empty.");

        if (isset($data['attributes'])) {
            $this->assertIsArray($data['attributes'], "block.json attributes for {$blockId} is not an array.");
        }
    }

    public function testAllExpectedBlocksHaveBlockJson(): void
    {
        foreach (self::EXPECTED_BLOCKS as $blockId) {
            $shortName = substr($blockId, strpos($blockId, '/') + 1);
            $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';
            $this->assertFileExists(
                $blockJsonPath,
                "Missing block.json for expected block {$blockId}."
            );
        }
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testEditorScriptFileReferenceFormat(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';
        $data = json_decode(file_get_contents($blockJsonPath), true);

        if (!isset($data['editorScript']) || !is_string($data['editorScript'])) {
            $this->markTestSkipped("No editorScript for {$blockId}.");
        }

        $ref = $data['editorScript'];
        $this->assertStringStartsWith('file:', $ref, "editorScript for {$blockId} must use file: prefix.");

        $relativePath = ltrim(substr($ref, 5), '/');
        $absolutePath = realpath(dirname($blockJsonPath) . '/' . $relativePath);
        if ($absolutePath !== false) {
            $this->assertFileExists($absolutePath, "editorScript file for {$blockId} not found: {$absolutePath}");
        }
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testStyleFileReferenceFormat(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';
        $data = json_decode(file_get_contents($blockJsonPath), true);

        if (!isset($data['style']) || !is_string($data['style'])) {
            $this->markTestSkipped("No style for {$blockId}.");
        }

        $ref = $data['style'];
        $this->assertStringStartsWith('file:', $ref, "style for {$blockId} must use file: prefix.");

        $relativePath = ltrim(substr($ref, 5), '/');
        $absolutePath = realpath(dirname($blockJsonPath) . '/' . $relativePath);
        if ($absolutePath !== false) {
            $this->assertFileExists($absolutePath, "style file for {$blockId} not found: {$absolutePath}");
        }
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testViewScriptFileReferenceFormat(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';
        $data = json_decode(file_get_contents($blockJsonPath), true);

        if (!isset($data['viewScript']) || !is_string($data['viewScript'])) {
            $this->markTestSkipped("No viewScript for {$blockId}.");
        }

        $ref = $data['viewScript'];
        $this->assertStringStartsWith('file:', $ref, "viewScript for {$blockId} must use file: prefix.");

        $relativePath = ltrim(substr($ref, 5), '/');
        $absolutePath = realpath(dirname($blockJsonPath) . '/' . $relativePath);
        if ($absolutePath !== false) {
            $this->assertFileExists($absolutePath, "viewScript file for {$blockId} not found: {$absolutePath}");
        }
    }

    /**
     * @dataProvider blockJsonProvider
     */
    public function testAttributesHaveValidTypes(string $blockId): void
    {
        $shortName = substr($blockId, strpos($blockId, '/') + 1);
        $blockJsonPath = self::BLOCKS_DIR . '/' . $shortName . '/block.json';
        $data = json_decode(file_get_contents($blockJsonPath), true);

        if (!isset($data['attributes']) || !is_array($data['attributes'])) {
            $this->markTestSkipped("No attributes defined for {$blockId}.");
        }

        $validTypes = ['string', 'number', 'boolean', 'array', 'object'];
        foreach ($data['attributes'] as $attrName => $attrDef) {
            if (!is_array($attrDef) || !isset($attrDef['type'])) {
                $this->fail("Attribute '{$attrName}' for {$blockId} missing 'type'.");
            }

            $type = $attrDef['type'];
            if (is_array($type)) {
                foreach ($type as $t) {
                    $this->assertContains(
                        $t,
                        $validTypes,
                        "Attribute '{$attrName}' for {$blockId} has invalid union type '{$t}'."
                    );
                }
            } else {
                $this->assertContains(
                    $type,
                    $validTypes,
                    "Attribute '{$attrName}' for {$blockId} has invalid type '{$type}'."
                );
            }
        }
    }

    public static function blockJsonProvider(): array
    {
        $data = [];
        foreach (self::EXPECTED_BLOCKS as $blockId) {
            $data[$blockId] = [$blockId];
        }
        return $data;
    }
}
