<?php

/**
 * Handler class
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
 * @version 1.0.3
 *
 */

namespace alonity\request;

class Handler {

    private $method, $url, $params, $options, $response;

    public function __construct(string $method, string $url, array $params = [], array $options = []){
        $this->setMethod(strtoupper($method))
            ->setURL($url)
            ->setParams($params)
            ->setOptions($options);
    }

    public function setMethod(string $method) : self {
        $this->method = strtoupper($method);

        return $this;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function setURL(string $url) : self {
        $this->url = $url;

        return $this;
    }

    public function getURL() : string {
        return $this->url;
    }

    public function setParams(array $params) : self {
        $this->params = $params;

        return $this;
    }

    public function getParams() : array {
        return $this->params;
    }

    public function setParam(string $key, $value) : self {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParam(string $key) {
        return $this->params[$key] ?? null;
    }

    public function setOptions(array $options) : self {
        $this->options = $options;

        return $this;
    }

    public function getOptions() : array {
        return $this->options;
    }

    public function setOption(string $key, $value) : self {
        $this->options[$key] = $value;

        return $this;
    }

    public function getOption(string $key) {
        return $this->options[$key] ?? null;
    }

    public function getResponse(){
        return $this->response;
    }

    public function setResponse($data) : self {
        $this->response = $data;

        return $this;
    }

    public function send(){
        $url = $this->url;

        if($this->method == 'GET'){
            $parse = parse_url($this->url);

            $path = $parse['path'] ?? '';

            $url = "{$parse['scheme']}://{$parse['host']}{$path}";

            $q = $parse['query'] ?? '';

            parse_str($q, $query);

            $data = array_merge_recursive($query, $this->params);

            if(!empty($data)){
                $url .= '?'.http_build_query($data);
            }
        }

        $c = curl_init($url);

        $curlv = curl_version();

        $v = Request::VERSION;

        $opts = [
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $this->options['headers'] ?? [],
            CURLOPT_CONNECTTIMEOUT => $this->options['timeout'] ?? 3,
            CURLOPT_TIMEOUT => $this->options['timeout'] ?? 3,
            CURLOPT_MAXREDIRS => $this->options['redirects'] ?? 3,
            CURLOPT_USERAGENT => $this->options['useragent'] ?? "Curl/{$curlv['version']} (Alonity Request/{$v})"
        ];

        if(isset($this->options['header'])){
            $opts[CURLOPT_HEADER] = $this->options['header'];
        }

        if($this->method == 'POST'){
            $opts[CURLOPT_POSTFIELDS] = http_build_query($this->params);
        }

        curl_setopt_array($c, $opts);

        $this->setResponse(curl_exec($c));

        curl_close($c);

        return $this->getResponse();
    }
}

?>