<?php

// +----------------------------------------------------------------------
// | LinkPHP [ Link All Thing ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://linkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liugene <liujun2199@vip.qq.com>
// +----------------------------------------------------------------------
// |               配置类
// +----------------------------------------------------------------------

namespace linkphp\http;

use Closure;

class Input
{

    /**
     * @var array 环境变量
     */
    protected $env;

    /**
     * @var array 请求参数
     */
    protected $param   = [];
    protected $get     = [];
    protected $post    = [];
    protected $request = [];
    protected $put;
    protected $session = [];
    protected $file    = [];
    protected $cookie  = [];
    protected $server  = [];
    protected $header  = [];

    protected $input  = [];

    private $filter;

    /**
     * @var HttpRequest
     */
    private $http_request;

    public function __construct()
    {
//        $this->http_request = $httpRequest;
        // 保存 php://input
        $this->input = file_get_contents('php://input');
    }

    public function get($key='',$filter)
    {
        if (empty($this->get)) {
            $this->get = $_GET;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->get = array_merge($this->get, $key);
        }
        return $key=='' ? $this->param($this->get,$filter) : $this->param($this->get[$key],$filter);
    }

    public function post($key='',$filter)
    {
        if (empty($this->post)) {
            $this->post = $_POST;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->post = array_merge($this->post, $key);
        }
        return $key=='' ? $this->param($this->post,$filter) : $this->param($this->post[$key],$filter);
    }

    public function server($key='')
    {
        if (empty($this->server)) {
            $this->server = $_SERVER;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->server = array_merge($this->server, $key);
        }
        return $key=='' ? $this->server : $this->server[$key];
    }

    public function file($key='')
    {
        if (empty($this->file)) {
            $this->file = $_FILES;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->file = array_merge($this->file, $key);
        }
        return $key=='' ? $this->file : $this->file[$key];
    }

    /**
     * 设置获取COOKIE参数
     * @access public
     * @param string|array      $key 变量名
     * @param string|array      $filter 过滤方法
     * @return mixed
     */
    public function cookie($key='', $filter = '')
    {
        if (empty($this->cookie)) {
            $this->cookie = $_COOKIE;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->cookie = array_merge($this->cookie, $key);
        }
        return $key=='' ? $this->cookie : $this->cookie[$key];
    }

    /**
     * 设置获取ENV参数
     * @access public
     * @param string|array      $key 变量名
     * @param string|array      $filter 过滤方法
     * @return mixed
     */
    public function env($key='', $filter = '')
    {
        if (empty($this->env)) {
            $this->env = $_ENV;
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->env = array_merge($this->env, $key);
        }
        return $key=='' ? $this->env : $this->env[$key];
    }

    /**
     * 设置或者获取当前的Header
     * @access public
     * @param string|array  $name header名称
     * @param string        $default 默认值
     * @return string
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     * 设置获取PUT参数
     * @access public
     * @param string|array      $key 变量名
     * @param string|array      $filter 过滤方法
     * @return mixed
     */
    public function put($key = '', $filter = '')
    {
        if (is_null($this->put)) {
            $content = $this->input;
            if (false !== strpos($this->http_request->contentType(), 'application/json')) {
                $this->put = (array) json_decode($content, true);
            } else {
                parse_str($content, $this->put);
            }
        }
        if (is_array($key)) {
            $this->param      = [];
            return $this->put = is_null($this->put) ? $key : array_merge($this->put, $key);
        }

        return $key =='' ? $this->param($this->put,$filter) : $this->param($this->put[$key],$filter);
    }

    /**
     * 获取request变量
     * @param string        $key 数据名称
     * @param string|array  $filter 过滤方法
     * @return mixed
     */
    public function request($key = '', $filter = '')
    {
        if (empty($this->request)) {
            $this->request = $_REQUEST;
        }
        if (is_array($key)) {
            $this->param          = [];
            return $this->request = array_merge($this->request, $key);
        }
        return $key =='' ? $this->param($this->request,$filter) : $this->param($this->request[$key],$filter);
    }

    /**
     * 获取session数据
     * @access public
     * @param string|array  $key 数据名称
     * @param string|array  $filter 过滤方法
     * @return mixed
     */
    public function session($key = '', $filter = '')
    {
        if (empty($this->session)) {
            $this->session = Session::get();
        }
        if (is_array($key)) {
            return $this->session = array_merge($this->session, $key);
        }
        return $key =='' ? $this->param($this->session,$filter) : $this->param($this->session[$key],$filter);
    }

    public function getInput($filter)
    {
        return $this->param($this->input,$filter);
    }

    public function param($data,$filters)
    {
        $filter = $this->getFilter($filters);
        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data,'',$filter);
        }
        return $data;
    }

    private function getFilter($filter)
    {
        $filter = $filter ?: $this->filter();
        if (is_string($filter) && false === strpos($filter, '/')) {
            $filter = explode(',', $filter);
        } else {
            $filter = (array) $filter;
        }
        return $filter;
    }

    public function filter($filter=null)
    {
        if (is_null($filter)) {
            return $this->filter;
        } else {
            $this->filter = $filter;
        }
    }

    public function filterValue(&$value,$key,$filters)
    {
        foreach($filters as $filter){
            if($filter instanceof Closure){
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            }
        }
        return $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式
     * @param string $value
     * @return void
     */
    public function filterExp(&$value)
    {
        $value = trim($value);
        // 过滤查询特殊字符
        if (is_string($value)) {
            $value = preg_replace('/^EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN$/i','', $value);
            $value = trim($value);
        }
        // TODO 其他安全过滤
    }

}
