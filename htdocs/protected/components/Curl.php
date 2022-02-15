<?php

/**
 * Отправка Url запросов через функции curl
 * Class Curl
 *
 * @author Aleksandr Roik
 */
class Curl extends CComponent
{
    /**
     * Параметры конфигурации
     *
     * @var array
     */
    public $config = [];

    /**
     * Результат выпослнения
     *
     * @var string|null|bool
     */
    protected $result;

    /**
     * Transport constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if ($config !== null) {
            $this->config = $config;
        }
    }

    /**
     * init
     */
    public function init()
    {

    }

    /**
     * Установка элемента конфига
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setConfigItem($key, $value)
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Возвращает "голый" результат
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Возвращает статус выполнения запроса
     *
     * @return bool
     */
    public function isErrorSend()
    {
        return $this->result === false ? true : false;
    }

    /**
     * Возвращает статус выполнения
     *
     * @return bool
     */
    public function statusSend()
    {
        return !$this->isErrorSend();
    }


    /**
     * Выполняет get запрос
     *
     * @param mixed $url
     * @return Curl
     */
    public function sendGet($url)
    {
        $this->result = $this->send($url);

        return $this;
    }

    /**
     * Выполняет post запрос
     *
     * @param mixed $url
     * @param array|null $data
     * @return Curl
     */
    public function sendPost($url, array $data = null)
    {
        $this->result = $this->send($url, $data);

        return $this;
    }

    /**
     * Выполнение CURL запроса
     *
     * @param string $url
     * @param array|null $postData
     * @return bool|string
     */
    protected function send($url, array $postData = null)
    {
        $curl = curl_init($url);
        //Настойка опций cookie

        // TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive, PHP will follow as many "Location: " headers that it is sent, unless CURLOPT_MAXREDIRS is set).
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, $this->config['encoding']); //Установка gzip

        // referer
        if ($this->config['referer']['enable']) {
            curl_setopt($curl, CURLOPT_REFERER, $this->config['referer']['url']);
            // TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        }

        // The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config['opt_timeout']);

        // Max redirects
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->config['opt_maxredirs']);

        // Прокси
        if ($this->config['proxy_server']['enable']) {
            curl_setopt($curl, CURLOPT_PROXY, $this->config['proxy_server']['address']);
            curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->config['proxy_server']['userpwd']);
        }

        // Куки
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->config['cookie_file_path']);//сохранить куки в файл
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->config['cookie_file_path']);//считать куки из файла

        // User-agent
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());

        // установка тела запроса
        if ($postData) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }

        // Headers
        if ($headers = $this->getHeaders()) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $raw = curl_exec($curl);
        curl_close($curl);

        return $raw;
    }

    /**
     * Возвращает список заголовков
     *
     * @return array
     */
    protected function getHeaders()
    {
        return $this->config['headers'];
    }

    /**
     * Возвращает Агент (браузер)
     *
     * @param int $session
     * @return string
     */
    protected function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Android') === false) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        $userAgents = $this->config['user_agents'];

        return $userAgents[mt_rand(0, count($userAgents) - 1)];
    }
}
