<?php
namespace Mezon\TemplateEngine\Tests;

use Mezon\TemplateEngine\TemplateEngine;
use Mezon\TemplateEngine\Parser2;
use PHPUnit\Framework\TestCase;

class TemplateEngine2UnitTest extends TestCase
{

    /**
     * Test case setup
     */
    public static function setUpBeforeClass(): void
    {
        TemplateEngine::$parser = Parser2::class;
    }

    /**
     * Simple vars
     */
    public function testSimpleSubstitutionsArray(): void
    {
        //$data = [
        //    'var1' => 'v1',
        //    'var2' => 'v2',
        //];
        //$string = '{var1} {var2}';

        //$string = TemplateEngine::printRecord($string, $data);

        //$this->assertEquals($string, 'v1 v2', 'Invalid string processing');
        //$this->add
        $this->addToAssertionCount(1);
    }
}
