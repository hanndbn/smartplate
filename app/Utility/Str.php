<?php

/**
 * String helper class
 *  
 * @package       app.Utility
 * 
 */
class Utility_Str {

    /**
     * Returns a string that is snipped to $maxLength characters at the nearest space,
     * Appends an elipsis if the string is snipped.
     *
     * @param string
     * @param integer Max length of returned string, excluding elipsis
     * @param integer Offset from string start - will add leading elipsis
     * @param string Elipses string (default: '...')
     *
     * @return string
     */
    public static function wordTrim($string, $maxLength, $offset = 0, $elipses = '...') {
        if ($offset) {
            $string = preg_replace('/^\S*\s+/s', '', utf8_substr($string, $offset));
        }

        $strLength = utf8_strlen($string);
        if ($maxLength > 0 && $strLength > $maxLength) {
            $string = (mb_strlen($string, 'UTF-8') != strlen($string)) ? utf8_substr($string, 0, $maxLength). $elipses : utf8_substr($string, 0, $maxLength*2). $elipses;
//            $string = utf8_substr($string, 0, $maxLength);
//            $string = strrev(preg_replace('/^\S*\s+/s', '', strrev($string))) . $elipses;          
        }

        if ($offset) {
            $string = $elipses . $string;
        }

        return $string;
    }

    /**
     * This method escapes the output before render to HTML.
     *
     * @param string $text Text to replace in
     * @param mixed $escapeCallback Callback for escaping. If empty, no escaping is done.
     *
     * @return string
     */
    public static function escapehtml($text, $escapeCallback = '') {
        if ($escapeCallback) {
            if ($escapeCallback == 'htmlspecialchars') {
                $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            } else {
                $text = call_user_func($escapeCallback, $text);
            }
        } else {
            $search = array('<', '>', '/', '"');
            $replace = array('&lt;', '&gt;', '&#47;', '&quot;');

            $text = str_replace($search, $replace, $text);
        }

        return $text;
    }
    
    /**
     * This method return the output before render to HTML.
     *
     * @param string $text Text to replace in
     * @param mixed $escapeCallback Callback for returning. If empty, no returning is done.
     *
     * @return string
     */
    public static function returnhtml($text, $escapeCallback = '') {
        if ($escapeCallback) {
            if ($escapeCallback == 'htmlspecialchars') {
                $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            } else {
                $text = call_user_func($escapeCallback, $text);
            }
        } else {
            $search = array('&lt;', '&gt;', '&#47;', '&quot;');
            $replace = array('<', '>', '/', '"');

            $text = str_replace($search, $replace, $text);
        }

        return $text;
    }

}