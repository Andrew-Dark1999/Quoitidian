<?php

/*
 * This file is part of the Fetch package.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Fetch;

/**
 * This library is a wrapper around the Imap library functions included in php.
 *
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 * @author  Sergey Linnik <linniksa@gmail.com>
 */
final class MIME
{
    /**
     * @param string $text
     * @param string $targetCharset
     *
     * @return string
     */

    protected static function checkUtf8($charset){
        if(strtolower($charset) != "utf-8" && strtolower($charset) != "utf8"){
            return false;
        }
        return true;
    }



    public static function decode($text, $targetCharset = 'utf-8')
    {
        if (null === $text) {
            return null;
        }

        $result = '';

        foreach (imap_mime_header_decode($text) as $word) {
            if(!self::checkUtf8($word->charset)){
                $charset = $word->charset;
                if($charset == 'default'){
                    $charset = 'utf-8';
                }
                try {
                    $result .= iconv(strtolower($charset), $targetCharset, $word->text);
                } catch(Error $e){
                    $result .= $word->text;
                } catch(Exception $e){
                    $result .= $word->text;
                }
            } else {
                $result .= $word->text;
            }
        }

        return $result;
    }
}
