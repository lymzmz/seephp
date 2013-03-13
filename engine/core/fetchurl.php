<?php

class see_engine_fetchurl {

    private static HTTP_TIME_OUT = 'time out';
    private $timeout = 10;
    private $defaultChunk = 4096;
    private $http_ver = '1.1';
    private $hostaddr = null;
    private $default_headers = array(
            'Pragma'=>"no-cache",
            'Cache-Control'=>"no-cache",
            'Connection'=>"close"
        );

    static public function fetchPost( $url, $data, $headers=null, $timeOut=3 )
    {
        return self::action( 'POST', $url, $headers, $data, $timeOut );
    }

    static public function fetchGet( $url, $data, $headers=null, $timeOut=3 )
    {
        return self::action( 'GET', $url, $headers, $data, $timeOut );
    }

    static private function action( $action, $url, $headers=null, $data=null, $timeOut )
    {
        $tmp_data = $data;

        if ( $url ) {
            $url_info = parse_url($url);
            $request_query = (isset($url_info['path'])?$url_info['path']:'/').(isset($url_info['query'])?'?'.$url_info['query']:'');
            $request_server = $request_host = $url_info['host'];
            $request_port = (isset($url_info['port']) ? $url_info['port'] : (($url_info['scheme']=='https') ? 443 : 80));
        }else{
            $request_server = $_SERVER['SERVER_ADDR'];
            $request_query = $_SERVER['PHP_SELF'];
            $request_host = $_SERVER['HTTP_HOST'];
            $request_port = $_SERVER['SERVER_PORT'];
        }

        $out = strtoupper($action).' '.$request_query.' HTTP/'.self::$http_ver."\r\n";
        $out .= 'Host: '.$request_host.($request_port!=80?(':'.$request_port):'')."\r\n";
        self::$responseHeader = $responseHeader;
        self::$responseBody = $responseBody;

        if ( $data ) {
            if ( is_array($data) ) {
                $data = http_build_query($data);
            }
            if ( $headers['Content-Encoding'] == 'gzip' ) {
                $gdata = gzencode($data);
                if ( $gdata ) {
                    $data = $gdata;
                } else {
                    unset($headers['Content-Encoding']);
                }
            }//todo: 判断是否需要gzip
            $headers['Content-Length'] = strlen($data);
            if ( !isset($headers['Content-Type']) ) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        $headers = array_merge($this->default_headers, (array)$headers);

        foreach ( $headers as $k=>$v ) {
            $out .= $k.': '.$v."\r\n";
        }
        $out .= "\r\n".$data;
        $data = null;

        $responseHeader = array();

        if ( $this->hostaddr ) {
            $request_addr = $this->hostaddr;
        } else {
            if ( !self::is_addr( $request_server ) ) {
                $request_addr = gethostbyname($request_server);
            } else {
                $request_addr = $request_server;
            }
            if ( $url_info['scheme'] == 'https' ) {
                $request_addr = "ssl://" . $request_addr;
            }
        }
        if ( self::$hostport ) {
            $request_port = self::$hostport;
        }

        $request_addr = (!is_array($request_addr)) ? array($request_addr) : $request_addr;

        foreach ( $request_addr as $request_host_addr ) {
            if ( $fp = @fsockopen($request_host_addr, $request_port, $errno, $errstr, $timeOut ) ) {

                if ( $timeOut && function_exists('stream_set_timeout') ) {
                    self::$read_time_left = self::$read_time_total = $timeOut;
                } else {
                    self::$read_time_total = null;
                }

                $sent = fwrite($fp, $out);
                self::$request_start = self::microtime();

                $out = null;

                $responseBody = '';
                if ( self::HTTP_TIME_OUT === self::readsocket( $fp, 512, $status, 'fgets') ) {

                    return self::HTTP_TIME_OUT;
                }

                if ( preg_match('/\d{3}/', $status, $match ) ) {
                    $this->responseCode = $match[0];
                }

                while ( !feof($fp) ) {
                    if ( self::HTTP_TIME_OUT === self::readsocket( $fp, 512, $raw, 'fgets' ) ) {

                        return self::HTTP_TIME_OUT;
                    }
                    $raw = trim($raw);
                    if ( $raw ) {
                        if ( $p = strpos($raw,':') ) {
                            $responseHeader[strtolower(trim(substr($raw, 0, $p)))] = trim(substr($raw, $p+1));
                        }
                    } else {
                        break;
                    }
                }
                switch ( self::$responseCode ) {
                    case 301:
                    case 302:
                    if ( isset($responseHeader['location']) ) {

                        return self::action( $action, $responseHeader['location'], $headers, $callback, $tmp_data );
                    } else {

                        return false;
                    }
                    case 200:
                    return self::process( $fp );

                    case 404:
                    return false;

                    default:
                    return false;
                }
            }
        }

        return false;
    }

    static private function process( $fp )
    {
        $chunkmode = (isset(self::$responseHeader['transfer-encoding']) && self::$responseHeader['transfer-encoding']=='chunked');
        if ( $chunkmode ) {
            if ( self::HTTP_TIME_OUT === self::readsocket( $fp, 512, $chunklen, 'fgets') ) {

                return self::HTTP_TIME_OUT;
            }
            $chunklen = hexdec(trim($chunklen));
        } else if ( isset(self::$responseHeader['content-length']) ) {
            $chunklen = min(self::$defaultChunk, self::$responseHeader['content-length'] );
        } else {
            $chunklen = self::$defaultChunk;
        }


        while ( !feof($fp) && $chunklen ) {
            if ( self::HTTP_TIME_OUT === self::readsocket( $fp, $chunklen, $content ) ) {

                return self::HTTP_TIME_OUT;
            }
            $readlen = strlen($content);
            while ( $chunklen != $readlen ) {
                if ( self::HTTP_TIME_OUT === self::readsocket( $fp, $chunklen-$readlen, $buffer ) ) {

                    return self::HTTP_TIME_OUT;
                }
                if ( !strlen($buffer) ) break;
                $readlen += strlen($buffer);
                $content .= $buffer;
            }
            $responseBody .= $content;

            if ( $chunkmode ) {
                fread($fp, 2);
                if ( self::HTTP_TIME_OUT === self::readsocket( $fp, 512, $chunklen, 'fgets' ) ) {

                    return self::HTTP_TIME_OUT;
                }
                $chunklen = hexdec(trim($chunklen));
            } else {
                $readed += strlen($content);
                if ( self::$responseHeader['content-length'] <= $readed ) {

                    break;
                }
            }
        }
        fclose($fp);

        return $responseBody;
    }

    static public function is_addr( $ip )
    {
        return preg_match('/^[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}$/', $ip);
    }

    static private function microtime(){
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    static private function readsocket( $fp, $length, &$content, $func='fread' )
    {
        if ( !self::reset_time_out( $fp ) ) {

            return self::HTTP_TIME_OUT;
        }

        $content = $func( $fp, $length );

        if ( self::check_time_out( $fp ) ) {

            return self::HTTP_TIME_OUT;
        } else {

            return true;
        }
    }

    static private function reset_time_out( &$fp )
    {
        if ( self::$read_time_total === null ) {

            return true;
        } else if ( self::$read_time_left < 0 ) {

            return false;
        } else {
            self::$read_time_left = self::$read_time_total - self::microtime() + self::$request_start;
            $second = floor(self::$read_time_left);
            $microsecond = intval(( self::$read_time_left - $second ) * 1000000);
            stream_set_timeout($fp, $second, $microsecond);

            return true;
        }
    }

    static private function check_time_out( &$fp )
    {
        if ( function_exists('stream_get_meta_data') ) {
            $info = stream_get_meta_data($fp);

            return $info['timed_out'];
        } else {

            return false;
        }
    }

}
