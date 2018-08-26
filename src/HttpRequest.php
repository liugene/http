<?php

namespace linkphp\http;

class HttpRequest
{

    /**
     * @var HttpResponse 响应对象
     */
    private $_response;

    /**
     * @var Input 参数接收对象
     */
    private $_input;

    /**
     * @var array 参数
     */
    private $queryParam = [];

    /**
     * @var string 请求方法
     */
    private $request_method;

    /**
     * @var array 数据
     */
    private $data;

    /**
     * @var string 请求根目录
     */
    private $root;

    /**
     * @var string 请求文件路径
     */
    private $baseFile;

    /**
     * @var string 请求URL
     */
    private $url;

    /**
     * @var string 请求域名
     */
    private $domain;

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

    /**
     * @var string|array|object 设置响应数据
     * @return $this
     */
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
            if($this->data instanceof HttpResponse){
                return $this->data->send(true);
            }
            return $this->_response->getDriver()
                ->setResponse(
                    $this->data
                )->send($return);
        }
        if($this->data instanceof HttpResponse){
            $this->data->send();
        }
        $this->_response->getDriver()
            ->setResponse(
                $this->data
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
     * 当前是否Ajax请求
     * @access public
     * @param bool $ajax  true 获取原始ajax请求
     * @return bool
     */
    public function isAjax($ajax = false)
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH');
        $result = ('xmlhttprequest' == $value) ? true : false;
        return $result;
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

    /**
     * 设置或获取URL访问根地址
     * @access public
     * @param string $url URL地址
     * @return string
     */
    public function root($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->root = $url;
            return $this;
        } elseif (!$this->root) {
            $file = $this->baseFile();
            if ($file && 0 !== strpos($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }
        return true === $url ? $this->domain() . $this->root : $this->root;
    }

    /**
     * 设置或获取当前执行的文件 SCRIPT_NAME
     * @access public
     * @param string $file 当前执行的文件
     * @return string
     */
    public function baseFile($file = null)
    {
        if (!is_null($file) && true !== $file) {
            $this->baseFile = $file;
            return $this;
        } elseif (!$this->baseFile) {
            $url = '';
            if (!IS_CLI) {
                $script_name = basename($_SERVER['SCRIPT_FILENAME']);
                if (basename($_SERVER['SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['SCRIPT_NAME'];
                } elseif (basename($_SERVER['PHP_SELF']) === $script_name) {
                    $url = $_SERVER['PHP_SELF'];
                } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['ORIG_SCRIPT_NAME'];
                } elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $script_name)) !== false) {
                    $url = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $script_name;
                } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                    $url = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                }
            }
            $this->baseFile = $url;
        }
        return true === $file ? $this->domain() . $this->baseFile : $this->baseFile;
    }

    /**
     * 设置或获取当前完整URL 包括QUERY_STRING
     * @access public
     * @param string|true $url URL地址 true 带域名获取
     * @return string
     */
    public function url($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->url = $url;
            return $this;
        } elseif (!$this->url) {
            if (IS_CLI) {
                $this->url = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $this->url = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->url = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $this->url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
            } else {
                $this->url = '';
            }
        }
        return true === $url ? $this->domain() . $this->url : $this->url;
    }

    /**
     * 设置或获取当前包含协议的域名
     * @access public
     * @param string $domain 域名
     * @return string
     */
    public function domain($domain = null)
    {
        if (!is_null($domain)) {
            $this->domain = $domain;
            return $this;
        } elseif (!$this->domain) {
            $this->domain = $this->scheme() . '://' . $this->host();
        }
        return $this->domain;
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        $server = array_merge($_SERVER, $this->server());
        if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
            return true;
        } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
            return true;
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
            return true;
        }
        return false;
    }

    /**
     * 当前请求的host
     * @access public
     * @return string
     */
    public function host()
    {
        if (isset($_SERVER['HTTP_X_REAL_HOST'])) {
            return $_SERVER['HTTP_X_REAL_HOST'];
        }
        return $this->server('HTTP_HOST');
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

    public function input($key = '',$filter = '')
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

        if(!$key){
            return $this->queryParam;
        }

        return isset($this->queryParam[$key]) ? $this->queryParam[$key] : false;
    }

    // 请求对象
    protected $_requester;

    // 设置请求对象
    public function setRequester($requester)
    {
        $this->_requester = $requester;
        // 重置数据
        $this->get(isset($requester->get) ? $requester->get : []);
        $this->post(isset($requester->post) ? $requester->post : []);
        $this->file(isset($requester->files) ? $requester->files : []);
        $this->cookie(isset($requester->cookie) ? $requester->cookie : []);
        $this->server(isset($requester->server) ? $requester->server : []);
        $this->header(isset($requester->header) ? $requester->header : []);
        return $this;
    }

    // 返回原始的HTTP包体
    public function getRawBody()
    {
        return $this->_requester->rawContent();
    }

}
