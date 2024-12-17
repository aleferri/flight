<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

namespace flight\http;

/**
 * The Response class represents an HTTP response. The object
 * contains the response headers, HTTP status code, and response
 * body.
 */
class Response {

    /**
     * @var int HTTP status
     */
    protected $status = 200;

    /**
     * @var array HTTP headers
     */
    protected $headers = [];

    /**
     * @var string HTTP response body
     */
    protected $body = '';

    /**
     *
     * @var string response file path
     */
    protected $file_path = '';

    /**
     *
     * @var bool continue dispatching
     */
    protected $complete = true;

    /**
     * @var bool HTTP response sent
     */
    protected $sent = false;

    /**
     * Chain the response
     */
    public function set_complete($complete): void {
        $this->complete = $complete;
    }

    /**
     *
     * @return bool
     */
    public function is_complete(): bool {
        return $this->complete;
    }

    /**
     * Sets the HTTP status of the response.
     *
     * @param int $code HTTP status code.
     * @return object|int Self reference
     * @throws \Exception If invalid status code
     */
    public function status(?int $code = null): self {
        if ( $code === null ) {
            return $this->status;
        }

        if ( array_key_exists( $code, HTTPStatus::CODES ) ) {
            $this->status = $code;
        } else {
            throw new \Exception( 'Invalid status code.' );
        }

        return $this;
    }

    /**
     * Adds a header to the response.
     *
     * @param string|array $name Header name or array of names and values
     * @param string $value Header value
     * @return object Self reference
     */
    public function header(string $name, ?string $value = null): self {
        if ( is_array( $name ) ) {
            foreach ( $name as $k => $v ) {
                $this->headers[ $k ] = $v;
            }
        } else {
            $this->headers[ $name ] = $value;
        }

        return $this;
    }

    public function file(string $path) {
        $this->file_path = $path;
    }

    /**
     * Returns the headers from the response
     * @return array
     */
    public function headers(): self {
        return $this->headers;
    }

    /**
     * Writes content to the response body.
     *
     * @param string $str Response content
     * @return object Self reference
     */
    public function write(string $str): self {
        $this->body .= $str;

        return $this;
    }

    /**
     * Clears the response.
     *
     * @return object Self reference
     */
    public function clear(): self {
        $this->status = 200;
        $this->headers = array();
        $this->body = '';

        return $this;
    }

    /**
     * Sets caching headers for the response.
     *
     * @param int|string $expires Expiration time
     * @return object Self reference
     */
    public function cache($expires): self {
        if ( $expires === false ) {
            $this->headers[ 'Expires' ] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $this->headers[ 'Cache-Control' ] = array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0'
            );
            $this->headers[ 'Pragma' ] = 'no-cache';
        } else {
            $expires = is_int( $expires ) ? $expires : strtotime( $expires );
            $this->headers[ 'Expires' ] = gmdate( 'D, d M Y H:i:s', $expires ) . ' GMT';
            $this->headers[ 'Cache-Control' ] = 'max-age=' . ($expires - time());
            if ( isset( $this->headers[ 'Pragma' ] ) && $this->headers[ 'Pragma' ] == 'no-cache' ) {
                unset( $this->headers[ 'Pragma' ] );
            }
        }
        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return object Self reference
     */
    public function sendHeaders() {
        // Send status code header
        if ( strpos( php_sapi_name(), 'cgi' ) !== false ) {
            header(
                sprintf(
                    'Status: %d %s', $this->status, HTTPStatus::CODES[ $this->status ]
                ), true
            );
        } else {
            header(
                sprintf(
                    '%s %d %s', (isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.1' ),
                    $this->status, HTTPStatus::CODES[ $this->status ]
                ), true, $this->status
            );
        }

        // Send other headers
        foreach ( $this->headers as $field => $value ) {
            if ( is_array( $value ) ) {
                foreach ( $value as $v ) {
                    header( $field . ': ' . $v, false );
                }
            } else {
                header( $field . ': ' . $value );
            }
        }

        // Send content length
        $length = $this->getContentLength();

        if ( $length > 0 ) {
            header( 'Content-Length: ' . $length );
        }

        return $this;
    }

    /**
     * Gets the content length.
     *
     * @return string Content length
     */
    public function getContentLength(): int {
        return extension_loaded( 'mbstring' ) ?
            mb_strlen( $this->body, 'latin1' ) :
            strlen( $this->body );
    }

    /**
     * Gets whether response was sent.
     */
    public function sent(): bool {
        return $this->sent;
    }

    /**
     * Sends a HTTP response.
     */
    public function send(): void {
        if ( ob_get_length() > 0 ) {
            ob_end_clean();
        }

        if ( ! headers_sent() ) {
            $this->sendHeaders();
        }

        if ( $this->file_path !== '' ) {
            readfile( $this->file_path );
        } else {
            echo $this->body;
        }

        $this->sent = true;
    }

}
