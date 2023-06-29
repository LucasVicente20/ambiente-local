<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 */
class InArrayRecursive
{

    /**
     * Checks if a value exists in an array
     * 
     * @link http://www.php.net/manual/en/function.in-array.php
     * @param mixed $needle
     *            <p>
     *            The searched value.
     *            </p>
     *            <p>
     *            If needle is a string, the comparison is done
     *            in a case-sensitive manner.
     *            </p>
     * @param array $haystack
     *            <p>
     *            The array.
     *            </p>
     * @param bool $strict
     *            [optional] <p>
     *            If the third parameter strict is set to true
     *            then the in_array function will also check the
     *            types of the
     *            needle in the haystack.
     *            </p>
     * @return bool true if needle is found in the array,
     *         false otherwise.
     */
    public static function run($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::run($needle, $item, $strict))) {
                return true;
            }
        }
        
        return false;
    }
}