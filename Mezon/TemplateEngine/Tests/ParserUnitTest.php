<?php
namespace Mezon\TemplateEngine\Tests;

use PHPUnit\Framework\TestCase;
use Mezon\TemplateEngine\Parser;

/** @psalm-suppress PropertyNotSetInConstructor */
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
        $this->assertFalse($result);
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
}
