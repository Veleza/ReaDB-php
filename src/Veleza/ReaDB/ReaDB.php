<?php

namespace Veleza\ReaDB;

class ReaDB
{

    private $context = null;
    private $host = null;
    private $req_port = 0;
    private $pub_port = 0;

    public function __construct($host, $req_port = 0, $pub_port = 0) {
        $this->host = $host;
        $this->req_port = $req_port;
        $this->pub_port = $pub_port;
    }

    private function createContext() {
        if (!$this->context) {
            $this->context = new ZMQContext(1);
        }
    }

    private function connectToReq() {
        if ($this->req_socket) {
            return;
        }
        if (!$this->host || !$this->req_port) {
            throw new Exception('Request requires a host and a req_port options to be set!');
        }
        $this->createContext();
        echo sprintf('%s:%s', $this->host, $this->req_port);
        // $this->req_socket = new ZMQSocket($this->context, ZMQ::SOCKET_REQ);
        // $this->req_socket->bind(sprintf('%s:%s', $this->host, $this->req_port));
    }

    public function request($cmd, $options) {
        $this->connectToReq();
    }

}


