<?php

namespace Ingest\Helper;

class Response
{
    public static function create($content)
    {
        $content = \Zend\Json\Json::encode($content);
        $response = new \Zend\Http\Response();
        $response->setContent($content);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
        $response->getHeaders()->addHeaderLine('Content-Length', strlen($content));

        return $response;
    }
}