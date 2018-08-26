<?php

namespace linkphp\http;

use linkphp\http\response\View;

class HttpResponse
{

    protected $status = 200;

    protected $content_type;

    protected $response;

    protected $header = [];

    protected $options = [];

    protected $charset = 'utf-8';

    /**
     * 构造函数
     * @access   public
     * @param mixed $data    输出数据
     * @param int   $status
     * @param array $header
     * @param array $options 输出参数
     */
    public function __construct($data = '', $status = 200, array $header = [], $options = [])
    {
        $this->setResponse($data);
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->contentType($this->content_type, $this->charset);
        $this->header = array_merge($this->header, $header);
        $this->status   = $status;
    }

    public function getResponse()
    {
        return $this->response;

    }

    /**
     * 页面输出类型
     * @param string $contentType 输出类型
     * @param string $charset     输出编码
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;
        return $this;
    }

    /**
     * 创建Response对象
     * @access public
     * @param mixed  $data    输出数据
     * @param string $type    输出类型
     * @param int    $code
     * @param array  $header
     * @param array  $options 输出参数
     * @return View
     */
    public static function create($data = '', $type = '', $code = 200, array $header = [], $options = [])
    {
        $type = empty($type) ? 'json' : strtolower($type);

        $class = false !== strpos($type, '\\') ? $type : '\\linkphp\\http\\response\\' . ucfirst($type);
        if (class_exists($class)) {
            $response = new $class($data, $code, $header, $options);
        } else {
            $response = new static($data, $code, $header, $options);
        }

        return $response;
    }

    public function send($return = false)
    {
        if($return) return $this->response;
        $this->header('Content-Type', $this->content_type . '; charset=utf-8');
        $status_header = 'HTTP/1.1 ' . $this->status . ' ' . Code::getStatusCodeMsg($this->status);
        if(!headers_sent()){
            //设置header头状态
            header($status_header);
            http_response_code($this->status);
            //设置header 头类型
            foreach ($this->header as $type => $value){
                header($type . ': ' . $value);
            }
        }
        echo $this->output($this->response);
        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
        exit;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function setContentType($type)
    {
        $this->content_type = $type;
        return $this;
    }

    /**
     * 设置响应头
     * @access public
     * @param string|array $name  参数名
     * @param string       $value 参数值
     * @return $this
     */
    public function header($name, $value = null)
    {
        if (is_array($name)) {
            $this->header = array_merge($this->header, $name);
        } else {
            $this->header[$name] = $value;
        }
        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getContentType()
    {
        return $this->content_type;
    }
}
