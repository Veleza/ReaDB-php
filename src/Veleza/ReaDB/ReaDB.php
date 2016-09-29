<?php

namespace Veleza\ReaDB;

class ReaDB
{

    private $id = null;

    private $host = null;
    private $req_port = 0;
    private $sub_port = 0;

    private $context = null;
    private $req_socket = null;
    private $sub_socket = null;

    public function __construct($host, $req_port = 0, $sub_port = 0) {
        $this->id = $this->uuid4();
        $this->host = $host;
        $this->req_port = $req_port;
        $this->sub_port = $sub_port;

        if (!$this->host) {
            throw new \Exception('ReaDB requires a host parameter to be set!');
        }

        if ($this->req_port) {
            $this->context = new \ZMQContext(1);
            $this->req_socket = new \ZMQSocket($this->context, \ZMQ::SOCKET_REQ);
            $this->req_socket->connect(sprintf('%s:%s', $this->host, $this->req_port));
            $this->req_socket->setSockOpt(\ZMQ::SOCKOPT_RCVTIMEO, 5000);
            $this->req_socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
        }
    }

public $reply = null;
public $duration = 0;

    public function request($cmd, $arguments=[]) {
        $start = microtime(true);
        $msg = [ $cmd, $arguments ];
        $data = snappy_compress(msgpack_pack($msg));
        $reply = $this->req_socket->send($data)->recv();
        if (!$reply) {
            throw new \Exception('Request timed out. ');
        }
        $result = msgpack_unpack(snappy_uncompress($reply));
        $this->duration = microtime(true) - $start;

        $this->reply = $reply;
        return $result;
    }

    private function uuid4() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

}


