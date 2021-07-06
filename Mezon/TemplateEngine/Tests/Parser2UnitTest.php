<?php
namespace Mezon\TemplateEngine\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\TemplateEngine\Parser;
use Mezon\TemplateEngine\Parser2;
use Mezon\TemplateEngine\TemplateEngine;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Parser2UnitTest extends TestCase
{

    /**
     *
     * {@inheritdoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        TemplateEngine::$parser = Parser2::class;
    }

    public static function tearDownAfterClass(): void
    {
        TemplateEngine::$parser = Parser::class;
    }
}
