<?php
namespace Mezon\TemplateEngine;

/**
 * Class TemplateEngine
 *
 * @package Mezon
 * @subpackage TemplateEngine
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/17)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Template engine class.
 */
class TemplateEngine
{

    /**
     * Parser to be used
     *
     * @var string
     */
    public static $parser = \Mezon\TemplateEngine\Parser::class;

    /**
     * Method replaces all {var-name} placeholders in $string with fields from $record
     *
     * @param string $string
     *            processing string
     * @param mixed $record
     *            printing record
     * @param
     *            string Processed string
     */
    public static function printRecord(string $string, $record): string
    {
        return self::$parser::printRecord($string, $record);
    }

    /**
     * Method unwraps data
     *
     * @param string $string
     *            processing string
     * @param mixed $record
     *            printing record
     * @param
     *            string Processed string
     */
    public static function unwrapBlocks(string $string, $record): string
    {
        return self::$parser::unwrapBlocks($string, $record);
    }

    /**
     * Method processes 'switch' macro
     *
     * @param string $string
     *            processing string
     * @return string Processed string
     */
    public static function compileSwitch($string): string
    {
        return self::$parser::compileSwitch($string);
    }
}
