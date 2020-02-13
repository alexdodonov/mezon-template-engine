<?php
namespace Mezon\TemplateEngine;

/**
 * Class Parser
 *
 * @package Mezon
 * @subpackage TemplateEngine
 * @author Dodonov A.A.
 * @version v.1.0 (2020/01/20)
 * @copyright Copyright (c) 2020, aeon.org
 */

/**
 * Parsing algorithms
 */
class Parser2
{

    /**
     * Method compiles string
     *
     * @param string $string
     *            string to be compiled
     * @param array|object $record
     *            set of vars to be applied
     * @return string compiled string
     */
    public static function compile(string $string, $record = []): string
    {
        $OpenBracePosition = 0;

        do {
            $OpenBracePosition = strpos($string, '{', $OpenBracePosition);

            if ($OpenBracePosition === false) {
                return $string;
            } else {
                $content = self::getContentInBraces($string, $OpenBracePosition);
            }
        } while (true);
    }

    /**
     * Method replaces all {var-name} placeholders in $string with fields from $record
     *
     * @param string $string
     *            processing string
     * @param array|object $record
     *            printing record
     * @param
     *            string Processed string
     */
    public static function printRecord(string $string, $record): string
    {
        if (is_array($record) === false && is_object($record) === false) {
            throw (new \Exception('Invalid record was passed'));
        }

        return self::compile($string, $record);
    }
}
