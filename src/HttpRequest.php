<?php

namespace linkphp\http;

class HttpRequest
{

    private $cmd;

    private $cmd_param = [];

    private $_response;

    private $_input;

    private $queryParam = [];

    //请求方法
    private $request_method;

    //数据
    private $data;

    private $request_http_accept = 'json';

    /**
     * @var array 资源类型
     */
    protected $mimeType = [
        'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*',
    ];

    public function __construct(ResponseDriver $response,Input $input)
    {
        $this->_response = $response;
        $this->_input = $input;
        //获取请求方式
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
        $this->setRequestMethod($request_method);
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->_response->setRequest($this);
        return $this;
    }

    public function setRequestMethod($method)
    {
        $this->request_method = $method;
        $this->_response->setRequest($this);
        return $this;
    }

    public function setRequestHttpAccept($accept)
    {
        $this->request_http_accept = $accept;
        $this->_response->setRequest($this);
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRequestMethod()
    {
        return $this->request_method;
    }

    public function getRequestHttpAccept()
    {
        return $this->request_http_accept;
    }

    /**
     * 响应请求返回response 实例对象
     * @return Object[ResponseDriver] $this->_response
     */
    public function setResponse()
    {
        $this->_response->getDriver()
            ->setResponse(
                $this->_response->getDriver()
                    ->output($this->data)
            );

        return $this->_response->getDriver();
    }

    /**
     * 响应请求输出结果
     * @param $return
     * @return $this->_request_http_accept
     */
    public function send($return = false)
    {
        if($return){
            return $this->_response->getDriver()
                ->setResponse(
                    $this->_response->getDriver()
                        ->output($this->data)
                )->send($return);
        }
        $this->_response->getDriver()
            ->setResponse(
                $this->_response->getDriver()
                    ->output($this->data)
            )->send($return);
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取客户端IP地址
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = true)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**x
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');
        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }
        return '';
    }

    /**
     * 设置资源类型
     * @access public
     * @param string|array  $type 资源类型名
     * @param string        $val 资源类型
     * @return void
     */
    public function mimeType($type, $val = '')
    {
        if (is_array($type)) {
            $this->mimeType = array_merge($this->mimeType, $type);
        } else {
            $this->mimeType[$type] = $val;
        }
    }

    /**
     * 获取当前请求的时间
     * @access public
     * @param bool $float 是否使用浮点类型
     * @return integer|float
     */
    public function time($float = false)
    {
        return $float ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    }

    /**
     * 当前请求的资源类型
     * @access public
     * @return false|string
     */
    public function type()
    {
        $accept = $this->server('HTTP_ACCEPT');
        if (empty($accept)) {
            return false;
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * 是否为method请求
     * @access public
     * @param $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getRequestMethod() === $method;
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('get');
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('post');
    }

    /**
     * 是否为DELETE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('delete');
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('put');
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('parch');
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod('head');
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod('options');
    }

    /**
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    public function setQueryParam()
    {
        $this->queryParam = array_merge(
            $this->get(),
            $this->post(),
            $this->file(),
            $this->server(),
            $this->cookie(),
            $this->env(),
            $this->header()
        );
        return $this;
    }

    public function get($key='',$filter='')
    {
        return $this->_input->get($key,$filter);
    }

    public function post($key='',$filter='')
    {
        return $this->_input->post($key,$filter);
    }

    public function request($key='',$filter='')
    {
        return $this->_input->request($key,$filter);
    }

    public function put($key='',$filter='')
    {
        return $this->_input->put($key,$filter);
    }

    public function session($key='',$filter='')
    {
        return $this->_input->session($key,$filter);
    }

    public function cookie($key='',$filter='')
    {
        return $this->_input->cookie($key,$filter);
    }

    public function file($key='')
    {
        return $this->_input->file($key);
    }

    public function server($key='')
    {
        return $this->_input->server($key);
    }

    public function env($key='')
    {
        return $this->_input->env($key);
    }

    public function header($key='')
    {
        return $this->_input->header($key);
    }

    public function getInput($filter='')
    {
        return $this->_input->getInput($filter);
    }

    public function input($key = '',$filter)
    {
        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key, 2);
            if (in_array($method, ['get', 'post', 'file', 'server', 'cookie', 'env'])) {
                return $this->$method($key,$filter);
            }
        }
        if(empty($this->queryParam)){
            $this->setQueryParam();
        }
        return $key=='' ? $this->queryParam : $this->queryParam[$key];
    }

    public function setCmdParam($command)
    {
        if(is_array($command) && count($command)>1){
            $this->cmd = $command[1];
        }
    }

    public function cmd($key)
    {
        return $this->cmd_param[$key];
    }

    // 请求对象
    protected $_requester;

    // 设置请求对象
    public function setRequester($requester)
    {
        $this->_requester = $requester;
        // 重置数据
        $_GET    = isset($requester->get) ? $requester->get : [];
        $_POST   = isset($requester->post) ? $requester->post : [];
        $_FILES  = isset($requester->files) ? $requester->files : [];
        $_COOKIE = isset($requester->cookie) ? $requester->cookie : [];
        $_SERVER = isset($requester->server) ? $requester->server : [];
        $this->header = isset($requester->header) ? $requester->header : [];
    }

    // 返回原始的HTTP包体
    public function getRawBody()
    {
        return $this->_requester->rawContent();
    }

}
