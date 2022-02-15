<?php
/**
* Translit
* Transliteration chars
* 
* @author Alex R.
*/ 


class Translit{
    
    private static $simplePairs = array('а' => 'a', 'л' => 'l', 'у' => 'u', 'б' => 'b', 'м' => 'm', 'т' => 't', 'в' => 'v', 'н' => 'n', 'ы' => 'y', 'г' => 'g', 'о' => 'o', 'ф' => 'f', 'д' => 'd', 'п' => 'p', 'и' => 'i', 'р' => 'r', 'А' => 'A', 'Л' => 'L', 'У' => 'U', 'Б' => 'B', 'М' => 'M', 'Т' => 'T', 'В' => 'V', 'Н' => 'N', 'Ы' => 'Y', 'Г' => 'G', 'О' => 'O', 'Ф' => 'F', 'Д' => 'D', 'П' => 'P', 'И' => 'I', 'Р' => 'R',);
    private static $complexPairs = array('з' => 'z', 'ц' => 'c', 'к' => 'k', 'ж' => 'zh', 'ч' => 'ch', 'х' => 'kh', 'е' => 'e', 'с' => 's', 'ё' => 'jo', 'э' => 'eh', 'ш' => 'sh', 'й' => 'jj', 'щ' => 'shh', 'ю' => 'ju', 'я' => 'ja', 'З' => 'Z', 'Ц' => 'C', 'К' => 'K', 'Ж' => 'ZH', 'Ч' => 'CH', 'Х' => 'KH', 'Е' => 'E', 'С' => 'S', 'Ё' => 'JO', 'Э' => 'EH', 'Ш' => 'SH', 'Й' => 'JJ', 'Щ' => 'SHH', 'Ю' => 'JU', 'Я' => 'JA', 'Ь' => "", 'Ъ' => "", 'ъ' => "", 'ь' => "",);
    private static $translitLatSymbols = array('a', 'l', 'u', 'b', 'm', 't', 'v', 'n', 'y', 'g', 'o', 'f', 'd', 'p', 'i', 'r', 'z', 'c', 'k', 'e', 's', 'A', 'L', 'U', 'B', 'M', 'T', 'V', 'N', 'Y', 'G', 'O', 'F', 'D', 'P', 'I', 'R', 'Z', 'C', 'K', 'E', 'S',);
    private static $specialSymbolsForTableName = array("_" => "_", "-"=>"_", "'" => "", "`" => "", "^" => "", " " => "_", '.' => '', ',' => '', ':' => '', '"' => '', "'" => '', '<' => '', '>' => '', '«' => '', '»' => '', ' ' => '_','('=>'_',')'=>'_','\\' => '','/'=>'','|'=>'','['=>'',']'=>'','{'=>'_','}'=>'_','~'=>'_','+'=>'_',';'=>'','?'=>'','*'=>'','&'=>'_','!'=>'','@'=>'','#'=>'','$'=>'','%'=>'','='=>'_');
    private static $specialSymbolsForFileName = array("_" => "_", "-"=>"-", "'" => "", "`" => "", "^" => "", " " => "_", '.' => '.', ',' => '_', ':' => '_', '"' => '', "'" => '', '<' => '', '>' => '', '«' => '', '»' => '', ' ' => '_','('=>'_',')'=>'_','\\' => '','/'=>'','|'=>'','['=>'',']'=>'','{'=>'_','}'=>'_','~'=>'_','+'=>'_',';'=>'','?'=>'','*'=>'','&'=>'_','!'=>'','@'=>'','#'=>'','$'=>'','%'=>'','='=>'_');    


    public static function forDataBase($text)
    {
        preg_match_all('/./u', $text, $text);
        $text = $text[0];
        $charsToTranslit = array_merge(array_keys(self::$simplePairs), array_keys(self::$complexPairs));
        $translitTable = array();
        foreach (self::$simplePairs as $key => $val) $translitTable[$key] = self::$simplePairs[$key];
        foreach (self::$complexPairs as $key => $val) $translitTable[$key] = self::$complexPairs[$key];
        foreach (self::$specialSymbolsForTableName as $key => $val) $translitTable[$key] = self::$specialSymbolsForTableName[$key];
        $result = "";
        $nonTranslitArea = false;
        foreach ($text as $char) {
            if (in_array($char, array_keys(self::$specialSymbolsForTableName))) {
                $result .= $translitTable[$char];
            } elseif (in_array($char, $charsToTranslit)) {
                if ($nonTranslitArea) {
                    $result .= "";
                    $nonTranslitArea = false;
                }
                $result .= $translitTable[$char];
            } else {
                if (!$nonTranslitArea && in_array($char, self::$translitLatSymbols)) {
                    $result .= "";
                    $nonTranslitArea = true;
                }
                $result .= $char;
            }
        }
        return preg_replace("/[-]{2,}/", '_', $result);
    }
        


    public static function forFileName($text)
    {
        preg_match_all('/./u', $text, $text);
        $text = $text[0];
        $charsToTranslit = array_merge(array_keys(self::$simplePairs), array_keys(self::$complexPairs));
        $translitTable = array();
        foreach (self::$simplePairs as $key => $val) $translitTable[$key] = self::$simplePairs[$key];
        foreach (self::$complexPairs as $key => $val) $translitTable[$key] = self::$complexPairs[$key];
        foreach (self::$specialSymbolsForFileName as $key => $val) $translitTable[$key] = self::$specialSymbolsForFileName[$key];
        $result = "";
        $nonTranslitArea = false;
        foreach ($text as $char) {
            if (in_array($char, array_keys(self::$specialSymbolsForFileName))) {
                $result .= $translitTable[$char];
            } elseif (in_array($char, $charsToTranslit)) {
                if ($nonTranslitArea) {
                    $result .= "";
                    $nonTranslitArea = false;
                }
                $result .= $translitTable[$char];
            } else {
                if (!$nonTranslitArea && in_array($char, self::$translitLatSymbols)) {
                    $result .= "";
                    $nonTranslitArea = true;
                }
                $result .= $char;
            }
        }
        return preg_replace("/[-]{2,}/", '_', $result);
    }
    
    
}