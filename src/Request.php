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
     * Send post request to url
     *
     * @param string $url
     * @param array $data
     * @param array $options
     *
     * @return Handler
     */
    public static function post(string $url, array $data = [], array $options = []) : Handler {
        return new Handler('POST', $url, $data, $options);
    }

    /**
     * Send get request to url
     *
     * @param string $url
     * @param array $data
     * @param array $options
     *
     * @return Handler
     */
    public static function get(string $url, array $data = [], array $options = []) : Handler {
        return new Handler('GET', $url, $data, $options);
    }

    /**
     * Send multiple requests in one moment
     *
     * @param array $requests
     *
     * @return Stack
     */
    public static function stack(array $requests) : Stack {
        return new Stack($requests);
    }
}

?>