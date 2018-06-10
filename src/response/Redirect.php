<?php

namespace linkphp\http\response;

use linkphp\http\HttpResponse;

class Redirect extends HttpResponse
{

    protected $content_type = 'application/html';

    /**
     * 处理数据
     * @access public
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    public function output($data)
    {
        $this->header['Location'] = $this->getTargetUrl();
        return;
    }

}