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
class Parser
{

    /**
     * Method returns starts and ends of the block
     *
     * @param array $positions
     *            Starting and ending positions of the blocks
     * @return array Updated positions
     */
    protected static function getPossibleBlockPositions(array &$positions): array
    {
        $startPos = $endPos = false;
        $c = 0;

        foreach ($positions as $key => $value) {
            if ($startPos === false && $value === 's') {
                $c ++;
                $startPos = $key;
            } elseif ($endPos === false && $value === 'e' && $c === 1) {
                $endPos = $key;
                break;
            } elseif ($value === 's' || $value === 'e' && $c > 0) {
                $c += $value === 's' ? 1 : - 1;
            }
        }

        return [
            $startPos,
            $endPos
        ];
    }

    /**
     * Method returns block's start and end
     *
     * @param string $string
     *            Parsing string
     * @param string $blockStart
     *            Block start
     * @param string $blockEnd
     *            Block end
     * @return array Starting and ending positions of the block
     */
    protected static function getAllBlockPositions(string $string, string $blockStart, string $blockEnd): array
    {
        $positions = [];
        $startPos = strpos($string, '{' . $blockStart . '}', 0);
        $endPos = - 1;

        if ($startPos !== false) {
            $positions[$startPos] = 's';
            $blockStart = explode(':', $blockStart);
            $blockStart = $blockStart[0];
            while (($startPos = strpos($string, '{' . $blockStart . ':', $startPos + 1)) !== false) {
                $positions[$startPos] = 's';
            }
        }
        while ($endPos = strpos($string, '{' . $blockEnd . '}', $endPos + 1)) {
            $positions[$endPos] = 'e';
        }
        ksort($positions);

        return $positions;
    }

    /**
     * Method returns block's start and end
     *
     * @param string $string
     *            Parsing string
     * @param string $blockStart
     *            Block start
     * @param string $blockEnd
     *            Block end
     * @return array Positions of the beginning and the end
     */
    public static function getBlockPositions(string $string, string $blockStart, string $blockEnd): array
    {
        $positions = self::getAllBlockPositions($string, $blockStart, $blockEnd);

        list ($startPos, $endPos) = self::getPossibleBlockPositions($positions);

        if ($startPos === false) {
            return [
                false,
                false
            ];
        }
        if ($endPos === false) {
            throw (new \Exception('Block end was not found'));
        }

        return [
            $startPos,
            $endPos
        ];
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
        $startPos = - 1;

        while (($parameters = self::getMacroParameters($string, 'switch', $startPos)) !== false) {
            if (self::areTerminalParams($parameters)) {
                $switchBody = self::getBlockData($string, "switch:$parameters", '~switch');

                $caseBody = self::getBlockData($switchBody, "case:$parameters", '~case');

                $string = self::replaceBlock($string, "switch:$parameters", '~switch', $caseBody);
            } else {
                $startPos = strpos($string, '{switch:', $startPos + 8);
            }
        }

        return $string;
    }

    /**
     * Method returns true if the params are terminal, false otherwise
     *
     * @param string $parameters
     *            Parameters to be analized
     * @return bool true if the params are terminal, false otherwise
     */
    protected static function areTerminalParams(string $parameters): bool
    {
        return strpos($parameters, '}') === false && strpos($parameters, '{') === false;
    }

    /**
     * Method fetches macro parameters
     *
     * @param string $string
     *            string to be parsed
     * @param string $name
     *            macro name
     * @param int $startPos
     *            starting position of the search
     * @return mixed Macro parameters or false if the macro was not found
     */
    public static function getMacroParameters(string $string, string $name, int $startPos = - 1)
    {
        while (($tmpStartPos = strpos($string, '{' . $name . ':', $startPos + 1)) !== false) {
            $counter = 1;
            $startPos = $tmpEndPos = $tmpStartPos;

            $macroStartPos = $startPos;
            $paramStartPos = $macroStartPos + strlen('{' . $name . ':');

            $result = self::findMacro(
                $string,
                $tmpStartPos,
                $tmpEndPos,
                $startPos,
                $counter,
                $macroStartPos,
                $paramStartPos);

            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Method returns content between {$blockStart} and {$blockEnd} tags
     *
     * @param string $string
     *            processing string
     * @param string $blockStart
     *            start of the block
     * @param string $blockEnd
     *            end of the block
     * @return mixed Block content. Or false if the block was not found
     */
    public static function getBlockData(string $string, string $blockStart, string $blockEnd)
    {
        list ($startPos, $endPos) = self::getBlockPositions($string, $blockStart, $blockEnd);

        if ($startPos !== false) {
            return substr(
                $string,
                $startPos + strlen('{' . $blockStart . '}'),
                $endPos - $startPos - strlen('{' . $blockStart . '}'));
        } else {
            return false;
        }
    }

    /**
     * Method replaces block with content
     *
     * @param string $str
     *            string to process
     * @param string $blockStart
     *            starting marker of the block
     * @param string $blockEnd
     *            ending marker of the block
     * @param string $content
     *            content to replace block
     * @param
     *            string Processed string
     */
    public static function replaceBlock($str, $blockStart, $blockEnd, $content)
    {
        list ($startPos, $endPos) = self::getBlockPositions($str, $blockStart, $blockEnd);

        if ($startPos !== false) {
            $str = substr_replace(
                $str,
                $content,
                $startPos,
                $endPos - $startPos + strlen(chr(123) . $blockEnd . chr(125)));
        }

        return $str;
    }

    /**
     * Getting macro start
     *
     * @param string $stringData
     *            Parsing string
     * @param int $tmpStartPos
     *            Search temporary starting position
     * @param int $tmpEndPos
     *            Search temporary ending position
     * @param int $startPos
     *            Search starting position
     * @param int $counter
     *            Brackets counter
     * @param int $macroStartPos
     *            Position of the macro
     * @param int $paramStartPos
     *            Position of macro's parameters
     * @return string Macro parameters or false otherwise
     */
    protected static function findMacro(
        &$stringData,
        &$tmpStartPos,
        &$tmpEndPos,
        &$startPos,
        &$counter,
        $macroStartPos,
        $paramStartPos)
    {
        do {
            self::handleMacroStartEnd($stringData, $tmpStartPos, $tmpEndPos, $startPos, $counter, $macroStartPos);

            if ($counter == 0) {
                return substr($stringData, $paramStartPos, $tmpEndPos - $paramStartPos);
            }
        } while ($tmpStartPos);

        return false;
    }

    /**
     * Getting macro start
     *
     * @param int $tmpStartPos
     *            Search temporary starting position
     * @param int $tmpEndPos
     *            Search temporary ending position
     * @param int $startPos
     *            Search starting position
     * @param int $counter
     *            Brackets counter
     */
    protected static function handleMacroStart(int $tmpStartPos, int $tmpEndPos, int &$startPos, int &$counter)
    {
        if ($tmpStartPos !== false && $tmpEndPos !== false) {
            if ($tmpStartPos < $tmpEndPos) {
                $startPos = $tmpEndPos;
            }
            if ($tmpEndPos < $tmpStartPos) {
                $counter --;
                if ($counter) {
                    $counter ++;
                }
                $startPos = $tmpStartPos;
            }
        }
    }

    /**
     * Getting macro end
     *
     * @param int $tmpStartPos
     *            Search temporary starting position
     * @param int $tmpEndPos
     *            Search temporary ending position
     * @param int $startPos
     *            Search starting position
     * @param int $counter
     *            Brackets counter
     * @param int $macroStartPos
     *            Position of the macro
     */
    protected static function handleMacroEnd(
        int $tmpStartPos,
        int $tmpEndPos,
        int &$startPos,
        int &$counter,
        int $macroStartPos)
    {
        if ($tmpStartPos !== false && $tmpEndPos === false) {
            $counter ++;
            $startPos = $tmpStartPos;
        }

        if ($tmpStartPos === false && $tmpEndPos !== false) {
            $counter --;
            $startPos = $tmpEndPos;
        }

        if ($tmpStartPos === false && $tmpEndPos === false) {
            /* nothing was found, so $startPos will be set with the length of $stringData */
            $startPos = $macroStartPos;
        }
    }

    /**
     * Getting macro bounds
     *
     * @param string $stringData
     *            Parsing string
     * @param int $tmpStartPos
     *            Search temporary starting position
     * @param int $tmpEndPos
     *            Search temporary ending position
     * @param int $startPos
     *            Search starting position
     * @param int $counter
     *            Brackets counter
     * @param int $macroStartPos
     *            Position of the macro
     */
    protected static function handleMacroStartEnd(
        &$stringData,
        &$tmpStartPos,
        &$tmpEndPos,
        &$startPos,
        &$counter,
        $macroStartPos)
    {
        $tmpStartPos = strpos($stringData, '{', $startPos + 1);
        $tmpEndPos = strpos($stringData, '}', $startPos + 1);

        self::handleMacroStart($tmpStartPos, $tmpEndPos, $startPos, $counter);

        self::handleMacroEnd($tmpStartPos, $tmpEndPos, $startPos, $counter, $macroStartPos);
    }

    /**
     * Method applyes data for foreach block content
     *
     * @param string $str
     *            string to process
     * @param string $parameters
     *            block parameters
     * @param mixed $data
     *            replacement data
     * @param
     *            string Processed string
     */
    protected static function applyForeachData($str, $parameters, $data)
    {
        $subTemplate = self::getBlockData($str, "foreach:$parameters", '~foreach');

        $blockStart = "{foreach:$parameters}";

        $recordCounter = 1;

        foreach ($data as $v) {
            $singleRecordTemplate = str_replace('{n}', $recordCounter ++, $subTemplate);

            $str = str_replace($blockStart, self::printRecord($singleRecordTemplate, $v) . $blockStart, $str);
        }

        return $str;
    }

    /**
     * Method applyes data for print block content
     *
     * @param string $str
     *            string to process
     * @param string $parameters
     *            block parameters
     * @param mixed $data
     *            replacement data
     * @param
     *            string Processed string
     */
    protected static function applyPrintData($str, $parameters, $data)
    {
        $subTemplate = self::getBlockData($str, "print:$parameters", '~print');

        $blockStart = "{print:$parameters}";

        return str_replace($blockStart, self::unwrapBlocks($subTemplate, $data) . $blockStart, $str);
    }

    /**
     * Method processes 'print' macro
     *
     * @param string $string
     *            processing string
     * @param mixed $record
     *            printing record
     * @return string Processed string
     */
    public static function compilePrint($string, &$record): string
    {
        $startPos = - 1;

        while ($parameters = self::getMacroParameters($string, 'print', $startPos)) {
            if (\Mezon\Functional\Functional::fieldExists($record, $parameters)) {
                $data = \Mezon\Functional\Functional::getField($record, $parameters);

                $string = self::applyPrintData($string, $parameters, $data);

                $string = self::replaceBlock($string, "print:$parameters", '~print', '');
            } else {
                $startPos = strpos($string, "{print:$parameters", $startPos > 0 ? $startPos : 0);
            }
        }

        return $string;
    }

    /**
     * Method processes 'foreach' macro
     *
     * @param string $string
     *            processing string
     * @param mixed $record
     *            printing record
     * @return string Processed string
     */
    public static function compileForeach($string, &$record): string
    {
        $startPos = - 1;

        while ($parameters = self::getMacroParameters($string, 'foreach', $startPos)) {
            if (\Mezon\Functional\Functional::fieldExists($record, $parameters)) {
                $data = \Mezon\Functional\Functional::getField($record, $parameters);

                $string = self::applyForeachData($string, $parameters, $data);

                $string = self::replaceBlock($string, "foreach:$parameters", '~foreach', '');
            } else {
                $startPos = strpos($string, "{foreach:$parameters", $startPos > 0 ? $startPos : 0);
            }
        }

        return $string;
    }

    /**
     * Method processes values substitution
     *
     * @param string $string
     *            processing string
     * @param mixed $record
     *            printing record
     * @return string Processed string
     */
    public static function compileValues($string, $record): string
    {
        foreach ($record as $field => $value) {
            if (is_array($value) || is_object($value)) {
                $string = self::unwrapBlocks($string, $value);
            } else {
                $string = str_replace('{' . $field . '}', $value, $string);
            }
        }

        return $string;
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
        $string = self::compilePrint($string, $record);

        $string = self::compileForeach($string, $record);

        return self::compileValues($string, $record);
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

        $string = self::unwrapBlocks($string, $record);

        return self::compileSwitch($string);
    }
}
