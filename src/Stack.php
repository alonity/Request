<?php

/**
 * Stack class
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

class Stack {

    private $requests;

    private $responses = [];

    public function __construct(array $requests){
        $this->setRequests($requests);
    }

    public function setRequests(array $requests) : self {
        $this->requests = $requests;

        return $this;
    }

    /**
     * @return Handler[]
    */
    public function getRequests() : array {
        return $this->requests;
    }

    /**
     * @return Handler[]
     */
    public function getResponses() : array {
        return $this->responses;
    }

    /**
     * @return Handler[]
    */
    public function send() : array {

        if(empty($this->requests)){ return $this->getResponses(); }

        $curlv = curl_version();

        $v = Request::VERSION;

        $multi = curl_multi_init();

        $channels = [];

        foreach($this->getRequests() as $k => $request) {

            $url = $request->getURL();

            $data = $request->getParams();

            if($request->getMethod() == 'POST'){
                $parse = parse_url($request->getURL());

                $url = "{$parse['scheme']}://{$parse['host']}";

                parse_str($parse['query'], $query);

                $data = array_merge_recursive($query, $request->getParams());

                if(!empty($data)){
                    $url .= '/?'.http_build_query($data);
                }
            }

            $options = $request->getOptions();

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

            if($request->getMethod() == 'POST'){
                $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
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

        foreach($this->getRequests() as $k => $request){
            $request->setResponse(curl_multi_getcontent($channels[$k]));

            $this->responses[] = $request;

            curl_multi_remove_handle($multi, $channels[$k]);
        }

        curl_multi_close($multi);

        return $this->getResponses();
    }
}

?>