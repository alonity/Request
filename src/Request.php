<?php

/**
 * Request class
 *
 *
 * @author Qexy admin@qexy.org
 *
 * @copyright © 2021 Alonity
 *
 * @package alonity\request
 *
 * @license MIT
 *
 * @version 1.0.0
 *
 */

namespace alonity\request;

use alonity\router\RequestInheritance;
use alonity\router\RequestInterface;

class Request extends RequestInheritance {
    private $uri;

    const VERSION = '1.0.0';

    /**
     * Set URI string
     *
     * @return self
     */
    public function setURI(string $uri) : RequestInterface {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get URI string
     *
     * @return string|null
     */
    public function getURI(): ?string {
        if(!is_null($this->uri)){ return $this->uri; }

        $this->uri = parent::getURI();

        return $this->uri;
    }

    /**
     * Send any method request to url
     *
     * @param string $type
     * @param string $url
     * @param array $data
     * @param array $options
     *
     * @return string|null|bool
    */
    public function send(string $type, string $url, array $data = [], array $options = []){
        $type = strtoupper($type);

        $c = curl_init($url);

        $curlv = curl_version();

        $v = self::VERSION;

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $options['headers'] ?? [],
            CURLOPT_CONNECTTIMEOUT => $options['timeout'] ?? 3,
            CURLOPT_TIMEOUT => $options['timeout'] ?? 3,
            CURLOPT_MAXREDIRS => $options['redirects'] ?? 3,
            CURLOPT_USERAGENT => $options['useragent'] ?? "Curl/{$curlv['version']} (Alonity Request/{$v})"
        ];

        if($type == 'POST'){
            $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        }

        curl_setopt_array($c, $opts);

        $result = curl_exec($c);

        curl_close($c);

        return $result;
    }

    /**
     * Send post request to url
     *
     * @param string $url
     * @param array $data
     * @param array $options
     *
     * @return string|null|bool
     */
    public function post(string $url, array $data = [], array $options = []) {
        return $this->send('POST', $url, $data, $options);
    }

    /**
     * Send get request to url
     *
     * @param string $url
     * @param array $data
     * @param array $options
     *
     * @return string|null|bool
     */
    public function get(string $url, array $data = [], array $options = []) {
        $parse = parse_url($url);

        $url = "{$parse['scheme']}://{$parse['host']}";

        parse_str($parse['query'], $query);

        $data = array_merge_recursive($query, $data);

        if(!empty($data)){
            $url .= '/?'.http_build_query($data);
        }

        return $this->send('GET', $url, $data, $options);
    }

    /**
     * Send multiple requests in one moment
     * WARNING! Array keys amount are be equals or empty
     *
     * @param array|null $types
     * @param array $urls
     * @param array $datas
     * @param array $options
     *
     * @return array
     */
    public function sendStack(?array $types, array $urls, array $datas = [], array $options = []) : array {

        $results = [];

        if(empty($urls)){ return $results; }

        $types = array_map('strtoupper', $types);

        $curlv = curl_version();

        $v = self::VERSION;

        $multi = curl_multi_init();

        $channels = [];

        foreach ($urls as $k => $url) {
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => $options['headers'] ?? [],
                CURLOPT_CONNECTTIMEOUT => $options['timeout'] ?? 3,
                CURLOPT_TIMEOUT => $options['timeout'] ?? 3,
                CURLOPT_MAXREDIRS => $options['redirects'] ?? 3,
                CURLOPT_USERAGENT => $options['useragent'] ?? "Curl/{$curlv['version']} (Alonity Request/{$v})"
            ]);

            $type = $types[$k] ?? 'GET';

            if($type == 'POST'){
                $opts[CURLOPT_POSTFIELDS] = http_build_query($datas[$k] ?? []);
            }

            curl_multi_add_handle($multi, $ch);

            $channels[$k] = $ch;
        }

        $active = null;

        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) == -1) {
                continue;
            }

            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($channels as $k => $channel) {
            $results[$k] = curl_multi_getcontent($channel);

            curl_multi_remove_handle($multi, $channel);
        }

        curl_multi_close($multi);

        return $results;
    }

    /**
     * @see sendStack
    */
    public function postStack(array $urls, array $datas = [], array $options = []) : array {
        $types = array_fill(0, count($urls), 'POST');

        return $this->sendStack($types, $urls, $datas, $options);
    }

    /**
     * @see sendStack
     */
    public function getStack(array $urls, array $datas = [], array $options = []) : array {
        $types = array_fill(0, count($urls), 'GET');

        $newurls = $newdatas = [];

        foreach($urls as $k => $url){
            $parse = parse_url($url);

            $newurls[$k] = "{$parse['scheme']}://{$parse['host']}";

            parse_str($parse['query'], $query);

            $newdatas[$k] = array_merge_recursive($query, $datas[$k] ?? []);

            if(!empty($newdatas[$k])){
                $newurls[$k] .= '/?'.http_build_query($newdatas[$k]);
            }
        }

        return $this->sendStack($types, $newurls, $newdatas, $options);
    }
}

?>