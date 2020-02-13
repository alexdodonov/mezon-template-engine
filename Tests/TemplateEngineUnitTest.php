<?php

class TemplateEngineUnitTest extends \PHPUnit\Framework\TestCase
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

        \Mezon\TemplateEngine\TemplateEngine::$parser = \Mezon\TemplateEngine\Parser::class;
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

    /**
     * Simple vars
     */
    public function testSimpleSubstitutionsObject(): void
    {
        $data = new stdClass();
        $data->var1 = 'v1';
        $data->var2 = 'v2';
        $string = '{var1} {var2}';

        $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, $data);

        $this->assertEquals($string, 'v1 v2', 'Invalid string processing');
    }

    /**
     * Invalid objects
     */
    public function testSimpleSubstitutionsInvalidObjects(): void
    {
        $msg = '';

        try {
            $string = '';
            $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, false);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals('Invalid record was passed', $msg, 'Invalid behavior');

        try {
            $string = '';
            $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, null);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals('Invalid record was passed', $msg, 'Invalid behavior');

        try {
            $string = '';
            $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, 1234);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals('Invalid record was passed', $msg, 'Invalid behavior');

        try {
            $string = '';
            $string = \Mezon\TemplateEngine\TemplateEngine::printRecord($string, 'string');
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals('Invalid record was passed', $msg, 'Invalid behavior');
    }

    /**
     * Data provider for testMacro
     *
     * @return array data sets
     */
    public function macroTestsData(): array
    {
        return json_decode(
            '[["{foreach:val}{~foreach}",[],"{foreach:val}{~foreach}"],["{print:val}{~print}",[],"{print:val}{~print}"],["{switch:1}{case:1}1{~case}{case:2}2{~case}{~switch}",[],"1"],["{foreach:field}{content}{~foreach}",{"field":[{"content":"1"},{"content":"2"}]},"12"],["{foreach:field}{n}{~foreach}",{"field":[{"f":1},{"f":2}]},"12"],["{switch:2}{case:1}1{~case}{case:2}2{~case}{~switch}",[],"2"],["{switch:0}{case:0}0{~case}{case:1}1{~case}{~switch}",[],"0"],["{switch:{value}}{case:0}0{~case}{case:1}1{~case}{~switch}",[],"{switch:{value}}{case:0}0{~case}{case:1}1{~case}{~switch}"],["{print:field}{content1}{content2}{~print}",{"field":[{"content1":"1"},{"content2":"2"}]},"12"],["{switch:{field3}}{case:3}Done!{~case}{~switch}",{"field1":1,"field2":{"f1":"11","f2":"22"},"field3":3},"Done!"],["{var1} {var2} {var3}",{"var1":"v1","var2":"v2","field":{"var3":"v3"}},"v1 v2 v3"]]',
            true);
    }

    /**
     * Method tests switch macro
     *
     * @dataProvider macroTestsData
     */
    public function testMacro(string $str, array $data, string $result): void
    {
        // setup and test body
        $data = \Mezon\TemplateEngine\TemplateEngine::printRecord($str, $data);

        // assertions
        $this->assertEquals($result, $data, 'Invalid blocks parsing');
    }
}
