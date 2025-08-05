<?php

namespace verbb\workflow\tests\unit\services;

use verbb\workflow\services\Content;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    private $contentService;

    protected function setUp(): void
    {
        $this->contentService = new Content();
    }

    public function testGetTextDiff()
    {
        $oldText = "Hello world\nThis is a test";
        $newText = "Hello universe\nThis is a test\nNew line added";

        $diff = $this->contentService->getTextDiff($oldText, $newText);

        $this->assertIsArray($diff);
        // Test should verify that differences are detected
    }

    public function testExtractPlainText()
    {
        // Test HTML stripping
        $content = "<p>Hello <strong>world</strong></p><br><div>Test content</div>";
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->contentService);
        $method = $reflection->getMethod('_extractPlainText');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->contentService, [$content]);
        
        $this->assertEquals('Hello world Test content', $result);
    }

    public function testExtractPlainTextWithWhitespace()
    {
        // Test whitespace normalization
        $content = "  Hello    world  \n\n  Test   ";
        
        $reflection = new \ReflectionClass($this->contentService);
        $method = $reflection->getMethod('_extractPlainText');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->contentService, [$content]);
        
        $this->assertEquals('Hello world Test', $result);
    }

    public function testEnhanceTextDiff()
    {
        $oldArray = [
            'fields' => [
                'title:1' => 'Old Title',
                'content:2' => '<p>Old content here</p>',
            ]
        ];
        
        $newArray = [
            'fields' => [
                'title:1' => 'New Title',
                'content:2' => '<p>New content here</p>',
            ]
        ];

        $diffChange = ['type' => 'change'];
        
        $reflection = new \ReflectionClass($this->contentService);
        $method = $reflection->getMethod('_enhanceTextDiff');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->contentService, [$diffChange, $oldArray, $newArray, 'title:1']);
        
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('change', $result['type']);
    }
}