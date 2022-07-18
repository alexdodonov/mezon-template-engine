<?php
namespace Mezon\TemplateEngine\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\TemplateEngine\Parser;
use Mezon\TemplateEngine\TemplateEngine;

/**
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ParserUnitTest extends TestCase
{

    /**
     * Testing getMacroParameters
     */
    public function testGetMacroParametersWithNestedVar(): void
    {
        // setup
        $string = 'some {foreach:{var}} string{~foreach}';

        // test body
        $result = Parser::getMacroParameters($string, "foreach");

        // assertions
        $this->assertEquals('{var}', $result);
    }

    /**
     * Testing getMacroParameters
     */
    public function testGetMacroParametersWithNestedMacro(): void
    {
        // setup
        $string = 'some {foreach:{switch:1}{case:1}{~case}{~switch}} string{~foreach}';

        // test body
        $result = Parser::getMacroParameters($string, "foreach");

        // assertions
        $this->assertEquals('{switch:1}{case:1}{~case}{~switch}', $result);
    }

    /**
     * Testing getMacroParameters
     */
    public function testGetMacroParameters(): void
    {
        // setup
        $string = 'some {foreach:var} string';

        // test body
        $result = Parser::getMacroParameters($string, "foreach");

        // assertions
        $this->assertEquals('var', $result);
    }

    /**
     * Testing case when macro was not found
     */
    public function testMacroNotFound(): void
    {
        // setup
        $string = 'some string';

        // test body
        $result = Parser::getMacroParameters($string, "foreach");

        // assertions
        $this->assertFalse($result);
    }

    /**
     * Testing method getBlockPositions for nested macros
     */
    public function testNestedMacros(): void
    {
        // setup
        $string = '{macro}{macro}{~macro}{~macro}';

        // test body
        list ($start, $end) = Parser::getBlockPositions($string, 'macro', '~macro');

        // assertions
        $this->assertEquals(0, $start);
        $this->assertEquals(22, $end);
    }
}
