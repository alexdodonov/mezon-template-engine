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
     * List of macro handlers
     *
     * @var array
     */
    public static $macroHandlers = [
        'foreach' => Parser2\ForeachHandler::parse,
        'switch' => Parser2\SwitchHandler::parse
    ];

    /**
     * Method returns macro name
     *
     * @param string $content
     *            content to be parsed
     * @param int $openBracePosition
     *            open brace position
     * @return string macro name
     */
    protected static function getMacroName(string $content, int $openBracePosition): string
    {
        // TODO make test when we try to fetch macro name from {placeholder} - it is not macro, it is placeholder!!!
        $doubleDotPosition = strpos($content, ':', $openBracePosition);
        $endBracePosition = strpos($content, '}', $openBracePosition);

        if ($endBracePosition === false) {
            throw (new \Exception('Invalid structure', - 1));
        } elseif ($doubleDotPosition === false || $endBracePosition < $doubleDotPosition) {
            // it is not macro, it is placeholder
            return '';
        } else {
            return substr($content, $openBracePosition + 1, $doubleDotPosition - $openBracePosition - 1);
        }
    }

    /**
     * Method returns read start
     *
     * @param string $content
     *            content to be processed
     * @param int $openBracePosition
     *            open brace position
     * @return int read cursor
     */
    protected static function getReadStart(string $content, int $openBracePosition): int
    {
        $counter = 1;

        while ($counter > 0) {
            $openBracePosition = strpos($content, '{', $openBracePosition + 1);
            $endBracePosition = strpos($content, '}', $openBracePosition + 1);

            if ($endBracePosition === null) {
                throw (new \Exception('Closing brace was not found', - 1));
            } elseif ($openBracePosition === null) {
                $counter --;
            } elseif ($openBracePosition < $endBracePosition) {
                $counter ++;
            } elseif ($openBracePosition > $endBracePosition) {
                $counter --;
            }
        }
    }

    /**
     * Method finds position of the final symbol to de read
     *
     * @param string $content
     *            content to be parsed
     * @param int $openBracePosition
     *            start of the macro
     * @return int position of the final symbol to de read
     */
    protected static function getReadEnd(string $content, int $openBracePosition): int
    {
        $counter = 1;
        $macroName = self::getMacroName($content, $openBracePosition);
        $macroStart = '{' . $macroName . (self::hasParameters($content, $openBracePosition) ? ':' : '}');
        $macroEnd = '{~' . $macroName . '}';

        while ($counter > 0) {
            $openMacroPosition = strpos($content, $macroStart, $openBracePosition + 1);
            $endMacroPosition = strpos($content, $macroEnd, $openBracePosition + 1);

            if ($endMacroPosition === false) {
                throw (new \Exception('Ending ' . $endMacroPosition . 'was not found', - 1));
            } elseif ($openMacroPosition === false) {
                // no open macros till the end of the $content
                $openBracePosition = $openMacroPosition;
                $counter --;
            } elseif ($openMacroPosition < $endMacroPosition) {
                // we have nested macros
                $openBracePosition = $openMacroPosition;
                $counter ++;
            } else {
                // $endMacroPosition < $openMacroPosition
                $openBracePosition = $endMacroPosition;
                $counter --;
            }
        }

        return $endMacroPosition;
    }

    /**
     * Method extracts content within macro
     *
     * @param string $content
     *            content to be parsed
     * @param int $openBracePosition
     *            starting position
     * @return string macro content
     */
    protected static function getContentInBraces(string $content, int $openBracePosition): string
    {
        $readStart = self::getReadStart($content, $openBracePosition);
        $readEnd = self::getReadEnd($content, $openBracePosition);

        return substr($content, $readStart, $readEnd - $readStart);
    }

    /**
     * Method substitutes macro
     *
     * @param string $string
     *            string to be processed
     * @param string $content
     *            already processed string
     * @param int $openBracePosition
     *            starting of the macro
     * @return string parsed
     */
    protected static function substituteMacro(string $string, string $content, int $openBracePosition): string
    {
        $macroName = self::getMacroName($string, $openBracePosition);
        $macroEnd = '{~' . $macroName . '}';
        $readEnd = self::getReadEnd($string, $openBracePosition);

        return substr_replace($string, $content, $openBracePosition, $readEnd - $openBracePosition + strlen($macroEnd));
    }

    /**
     * Method processes the specified macro
     *
     * @param string $macroName
     *            macro name
     * @param string $content
     *            content to be processed
     * @param array|object $record
     *            set of vars to be applied
     * @return string processed content
     */
    public static function processMacro(string $macroName, string $content, $record): string
    {
        if (isset(self::$macroHandlers[$macroName])) {
            return self::$macroHandlers[$macroName]($content, $record);
        } else {
            throw (new \Exception('Macro handler "' . $macroName . '" was not found', - 1));
        }
    }

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
        $openBracePosition = 0;
        $result = '';

        while (true) {
            $openBracePosition = strpos($string, '{', $openBracePosition);

            if ($openBracePosition === false) {
                $result = $string;
                break;
            } else {
                $macroName = self::getMacroName($string, $openBracePosition);
                $content = self::getContentInBraces($string, $openBracePosition);
                $content = self::processMacro($macroName, $content, $record);
                $string = self::substituteMacro($string, $content, $openBracePosition);
            }
        }

        return $result;
    }

    /**
     * Method replaces all {var-name} placeholders in $string with fields from $record
     *
     * @param string $string
     *            processing string
     * @param array|object|mixed $record
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
