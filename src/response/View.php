<?php

namespace linkphp\http\response;
use linkphp\http\HttpResponse;

class View extends HttpResponse
{

    protected $content_type = 'text/html';

    /**
     * 处理数据
     * @access public
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    public function output($data)
    {
        return $data;
    }

}