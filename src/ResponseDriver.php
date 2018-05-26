<?php

namespace linkphp\http;
use linkphp\http\response\Json;
use linkphp\http\response\Xml;
use linkphp\http\response\View;
use linkphp\http\response\Console;

class ResponseDriver
{

    /**
     * @var HttpRequest
     */
    private $_request;

    public function setRequest(HttpRequest $request)
    {
        $this->_request = $request;
    }

    public function getDriver()
    {
        switch($this->_request->getRequestHttpAccept()){
            case 'json':
                $response_driver = new Json();
                break;
            case 'xml':
                $response_driver = new Xml();
                break;
            case 'view':
                $response_driver = new view();
                break;
            case 'console':
                $response_driver = new Console();
                break;
            default :
                $response_driver = new Json();
        }
        return $response_driver;
    }

}
