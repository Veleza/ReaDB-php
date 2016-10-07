<?php

namespace Veleza\ReaDB;

class ReaDB
{

    private $host = null;
    private $port = null;
    private $timeout = null;

    private $zmq_req_context = null;
    private $zmq_req_socket = null;

    private $zmq_sub_context = null;
    private $zmq_sub_socket = null;

    private $context = null;

    public function __construct($host) {
        $this->host = $host;
        if (!$this->host) {
            throw new \Exception('ReaDB requires a host parameter to be set!');
        }
    }

    public function connect($port, $timeout = 5000) {
        $this->port = $port;
        $this->timeout = $timeout;
        if ($this->zmq_req_context) {
            throw new \Exception('ReaDB is already connected!');
        }
        $this->zmq_req_context = new \ZMQContext(1);
        $this->zmq_req_socket = new \ZMQSocket($this->zmq_req_context, \ZMQ::SOCKET_REQ);
        $this->zmq_req_socket->connect(sprintf('%s:%s', $this->host, $port));
        $this->zmq_req_socket->setSockOpt(\ZMQ::SOCKOPT_RCVTIMEO, $this->timeout);
        $this->zmq_req_socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
    }

    public function subscribe($port) {

    }

    public function setContext($context) {
        $this->context = $context;
    }

    public function getContext() {
        return $this->context;
    }

    public function get($model, $id, $structure = [], $raw = false) {
        $req = [
            'model' => $model,
            'id' => $id,
        ];

        if ($this->context) {
            $req['context'] = $this->context;
        }

        if ($structure && sizeof($structure)) {
            $req['structure'] = $structure;
        }

        return $this->request('get', $req, $raw);
    }

    public function request($cmd, $arguments=[], $raw = false) {
        $msg = [ $cmd, $arguments ];
        $data = $this->pack($msg);
        $reply = $this->zmq_req_socket->send($data)->recv();
        if (!$reply) {
            $this->zmq_req_socket = null;
            $this->zmq_req_context = null;
            $this->connect($this->port, $this->timeout);
            throw new \Exception('Request timed out. ');
        }
        if ($raw) {
            return $reply;
        }
        return $this->unpack($reply);
    }

    public function pack($msg) {
        return snappy_compress(msgpack_pack($msg));
    }

    public function unpack($msg) {
        return msgpack_unpack(snappy_uncompress($msg));
    }

}


