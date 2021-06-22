<?php

class TemplateEngine2UnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Constructor
     *
     * @param string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        \Mezon\TemplateEngine\TemplateEngine::$parser = \Mezon\TemplateEngine\Parser2::class;
    }

    /**
     * Simple vars
     */
    public function testSimpleSubstitutionsArray(): void
    {
        $data = [
            'var1' => 'v1',
            'var2' => 'v2',
        ];
        $string = '{var1} {var2}';

        $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, $data);

        $this->assertEquals($string, 'v1 v2', 'Invalid string processing');
    }
}
