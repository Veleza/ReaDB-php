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
    }

    public function request($cmd, $arguments=[]) {
        $this->connectToReq();
        $msg = [ $cmd, $arguments ];
        $data = snappy_compress(msgpack_pack($msg));
        $queue->send($data);
    }

    private function uuid4() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    private function createContext() {
        if (!$this->context) {
            $this->context = new \ZMQContext(1);
        }
    }

    private function connectToReq() {
        if ($this->req_socket) {
            return;
        }
        if (!$this->host || !$this->req_port) {
            throw new \Exception('Request requires a host and a req_port options to be set!');
        }
        $this->createContext();
        $this->req_socket = new \ZMQSocket($this->context, \ZMQ::SOCKET_REQ, $this->id);
        $this->req_socket->connect(sprintf('%s:%s', $this->host, $this->req_port));
    }

}


