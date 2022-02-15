<?php
/**
 * Helper
 * Other helpers methods
 *
 * @author Alex R.
 */

class Helper
{

    /**
     * Слияние  массивов с Добавлением/обновлением элементов второго массива к первому
     *
     * @param $array1 array
     * @param $array2 array
     * @return array
     */
    public static function arrayMerge($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (!is_array($value)) {
                $array1[$key] = $value;
            } else {
                if (isset($array1[$key])) {
                    $array1[$key] = self::arrayMerge($array1[$key], $value);
                } else {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }

    /**
     * удаление значений  переданых ключей
     *
     * @return array
     */
    public static function arrayDeleteValues($keys, $array)
    {
        if (!empty($keys)) {
            return $array;
        }
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * сортировка многомерных массивов
     *
     * @param $array
     * @param $key_sort
     * @return array
     */
    private static function build_sorter($key, $sort)
    {
        if ($sort == 'asc') {
            return function ($a, $b) use ($key) {
                return strnatcmp($a[$key], $b[$key]);
            };
        }
        if ($sort == 'desc') {
            return function ($a, $b) use ($key) {
                return strnatcmp($a[$key], $b[$key]) * -1;
            };
        }

    }

    public static function arraySort($array, $key_sort, $sort = 'asc')
    {
        usort($array, self::build_sorter($key_sort, $sort));

        return $array;
    }

    public static function strToUpper($str, $encode = 'utf-8')
    {
        return mb_strtoupper($str, $encode);
    }

    public static function strToLower($str, $encode = 'utf-8')
    {
        return mb_strtolower($str, $encode);
    }

    public static function formatDateTime($date_time)
    {
        if (!strtotime($date_time)) {
            return '';
        }

        return date(LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['medium'], strtotime($date_time));
    }

    public static function formatDateTimeShort($date_time)
    {
        if (!strtotime($date_time)) {
            return '';
        }

        return date(LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['medium_short'], strtotime($date_time));
    }

    public static function formatDate($date_time)
    {
        if (!strtotime($date_time)) {
            return '';
        }

        return date(LocaleCRM::getInstance2()->_data_p['dateFormats']['medium'], strtotime($date_time));
    }

    public static function formatTimeShort($date_time)
    {
        if (!strtotime($date_time)) {
            return '';
        }

        return date(LocaleCRM::getInstance2()->_data_p['timeFormats']['medium_short'], strtotime($date_time));
    }

    public static function truncateString($str, $length)
    {
        if (mb_strlen($str, 'utf-8') > $length) {
            return [
                'value' => mb_substr($str, 0, $length, 'utf-8') . '...',
                'title' => $str
            ];
        } else {
            return [
                'value' => $str,
                'title' => false
            ];
        }
    }

    /**
     * Возвращае ошибку валидации файла
     */
    public static function getFileError($errors, $field_name, $file_name)
    {
        if (!empty($errors) && isset($errors[$field_name])) {
            foreach ($errors[$field_name] as $value) {
                if (isset($value['file_name']) && $value['file_name'] == $file_name) {
                    return $value['message'];
                }
            }
        }
    }

    /**
     * удаляет все переводы строк
     */
    public static function deleteLinefeeds($string, $str_replace = '')
    {
        $string = str_replace("\r\n", $str_replace, $string);
        $string = str_replace("\n", $str_replace, $string);

        return $string;
    }

    /**
     * удаляет последние нули из строки
     */
    public static function TruncateEndZero($float)
    {
        $array = explode('.', $float);
        if (count($array) == 1) {
            return $array[0];
        }
        $array[1] = preg_replace('/[0]+$/', '', $array[1]);

        return $array[0] . ($array[1] !== '' ? '.' . $array[1] : '');
    }

    /**
     * поиск допустимых символов для даты
     */
    public static function checkCharForDate($str)
    {
        $result = true;
        $array = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '/', '_', '*', '\\', '.', ':', ' '];
        for ($i = 0; $i < strlen($str); $i++) {
            if (in_array((string)$str[$i], $array) == false) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * рекурсивный поиск по массиву значения и его замена
     */
    public static function strReplace($search, $replace, $data)
    {
        if (is_array($data)) {
            foreach ($data as &$element) {
                if (is_array($element)) {
                    $element = self::strReplace($search, $replace, $element);
                } else {
                    $element = self::mbStrReplace($search, $replace, $element);
                }
            }
            unset($element);
        } else {
            $data = self::mbStrReplace($search, $replace, $data);
        }

        return $data;
    }

    /**
     * mbStrReplace - подмена подстроки в строке
     *
     * @param $search
     * @param $replace
     * @param $subject
     * @return array|string
     */
    public static function mbStrReplace($search, $replace, $subject)
    {
        if (is_array($subject)) {
            $ret = [];
            foreach ($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }

            return $ret;
        }

        foreach ((array)$search as $key => $s) {
            if ($s == '') {
                continue;
            }

            if (!is_array($replace)) {
                $r = $replace;
            } else {
                if (is_array($search)) {
                    if (count($search) == count($replace)) {
                        $r = $replace[$key];
                    } else {
                        $r = implode('', $replace);
                    }

                } else {
                    $r = implode('', $replace);
                }
            }

            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while ($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }

        return $subject;
    }

    /**
     * arrayToXml - переводит массив в Xml
     *
     * @param $data
     * @param string $rootNodeName
     * @param null $xml
     * @return mixed
     */
    public static function arrayToXml($data, $rootNodeName = 'body', $xml = null, $first_node = true, $encoding = 'utf-8')
    {
        if (ini_get('zend.ze1_compatibility') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null) {
            $xml = simplexml_load_string('<?xml version="1.0" encoding="' . $encoding . '"?><' . $rootNodeName . '/>');
        }

        //цикл перебора массива
        foreach ($data as $key => $value) {
            // нельзя применять числовое название полей в XML
            if (is_numeric($key)) {
                // поэтому делаем их строковыми
                if ($first_node) {
                    $key = "section";// . ($key+1);
                } else {
                    $key = "row";// . ($key+1);
                }
            }

            // удаляем не латинские символы
            $key = preg_replace('/[^a-z0-9,_]/i', '', $key);

            // если значение массива также является массивом то вызываем себя рекурсивно
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // рекурсивный вызов
                self::arrayToXml($value, $rootNodeName, $node, false);
            } else {
                // добавляем один узел
                $value = htmlentities($value);
                $node = $xml->addChild($key, $value);
                //$node->addAttribute('name', $key);
            }
        }

        // возвратим XML
        $dom = new DOMDocument("1.0", $encoding);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();

    }

    public static function todisk($data, $file_name = 'crm_1', $type = 'txt')
    {
        if (is_array($data)) {
            $data = print_r($data, true);
        }
        file_put_contents('/var/tmp/' . $file_name . ($type ? '.' . $type : ''), $data);
    }

    public static function todiskWin($data, $file_name = 'crm_1', $type = 'txt')
    {
        if (is_array($data)) {
            $data = print_r($data, true);
        }
        file_put_contents('c:\\' . $file_name . ($type ? '.' . $type : ''), $data);
    }

    /**
     * getUniqueKey - генерирует по md5 и возвращает уникальный ключ
     */
    static private $uk_index = 1;

    public static function getUniqueKey($encode_to_md5 = true)
    {
        $str = date('YmdHis') . self::$uk_index;

        if ($encode_to_md5) {
            $str = md5($str);
        }

        self::$uk_index++;

        return $str;
    }

    /**
     * Замена символов для названий параметров для SQL запросов
     * В т.ч. используется для подмены "плохих" символов при создании имени перед сохранение нового модуля и его полей
     *
     * @param $str
     * @return string|null
     */
    public static function replaceToSqlParam($str)
    {
        if ($str === null || $str === '') {
            return $str;
        }

        $translitLatSymbols = ['a', 'l', 'u', 'b', 'm', 't', 'v', 'n', 'y', 'g', 'o', 'f', 'd', 'p', 'i', 'r', 'z', 'c', 'k', 'e', 's', 'A', 'L', 'U', 'B', 'M', 'T', 'V', 'N', 'Y', 'G', 'O', 'F', 'D', 'P', 'I', 'R', 'Z', 'C', 'K', 'E', 'S', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '_'];
        $result = '';

        for ($i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            if (in_array($char, $translitLatSymbols)) {
                $result .= $char;
            } else {
                $result .= '_r0_'; //_r0_ - типа - "replace"
            }
        }

        return $result;
    }

    /**
     * Для контроля латинских символов
     *
     * @param $str
     * @return bool
     */
    public static function hasNotLatynChars($str): bool
    {
        if ($str === null || $str === '') {
            return true;
        }

        $translitLatSymbols = ['a', 'l', 'u', 'b', 'm', 't', 'v', 'n', 'y', 'g', 'o', 'f', 'd', 'p', 'i', 'r', 'z', 'c', 'k', 'e', 's', 'A', 'L', 'U', 'B', 'M', 'T', 'V', 'N', 'Y', 'G', 'O', 'F', 'D', 'P', 'I', 'R', 'Z', 'C', 'K', 'E', 'S', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '_'];
        $result = '';

        for ($i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            if (!in_array($char, $translitLatSymbols)) {
                return false;
            }
        }

        return true;
    }


} 
