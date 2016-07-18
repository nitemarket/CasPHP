<?php
/**
 * CasPHP - a PHP 5 framework
 *
 * @author      Cas Chan <casper_ccb@hotmail.com>
 * @version     1.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class Core {
	protected $notFound;
    
    //response
    public $status;
    public $headers;
    public $body;
    
    //request
    public $cookies;
    public $settings;
    public $env;
    
    public $router;
    public $view;
	
	const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';
    
    protected static $special_header = array(
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    );
    
    protected static $messages = array(
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        226 => '226 IM Used',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required'
    );
	
	public function __construct($userSettings = array()){
		$method_code = '0001';
		
		$this->settings = array_merge(static::getDefaultSettings(), $userSettings);
        $this->env = $this->getEnvironmentVariable();
        $this->cookies = Util::stripSlashesIfMagicQuotes($_COOKIE);
        
        $this->router = new CoreRouter();
		$this->view = new CoreView();
	}
	
	public static function getDefaultSettings(){
        return array(
            'routes.case_sensitive' => true,
            //HTTP
            'http.version' => '1.1',
            //route
            'prePattern' => ''
        );
    }
    
    public function settings($name, $value = null){
        if (is_array($name)) {
            if (true === $value) {
                $this->settings = array_merge_recursive($this->settings, $name);
            } else {
                $this->settings = array_merge($this->settings, $name);
            }
        } elseif (func_num_args() === 1) {
            return isset($this->settings[$name]) ? $this->settings[$name] : null;
        } else {
            $this->settings[$name] = $value;
        }
    }
    
    /********************************************************************************
    * Environment Methods
    *******************************************************************************/
    
    protected function getEnvironmentVariable(){
        $env = array();

		$env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
		$env['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

		// Server params
		$scriptName = $_SERVER['SCRIPT_NAME'];
		$requestUri = $_SERVER['REQUEST_URI'];
		$queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

		// Physical path
		if (strpos($requestUri, $scriptName) !== false) {
			$physicalPath = $scriptName;
		} else {
			$physicalPath = str_replace('\\', '', dirname($scriptName));
		}
		$env['SCRIPT_NAME'] = rtrim($physicalPath, '/');

		// Virtual path
		$env['PATH_INFO'] = $requestUri;
		if (substr($requestUri, 0, strlen($physicalPath)) == $physicalPath) {
			$env['PATH_INFO'] = substr($requestUri, strlen($physicalPath));
		}
		$env['PATH_INFO'] = str_replace('?' . $queryString, '', $env['PATH_INFO']);
		$env['PATH_INFO'] = '/' . ltrim($env['PATH_INFO'], '/');

		$env['QUERY_STRING'] = $queryString;
		$env['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
		$env['SERVER_PORT'] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
		$env['url_scheme'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

		//HTTP request headers (retains HTTP_ prefix to match $_SERVER)
        foreach ($_SERVER as $key => $value) {
			$key = strtoupper($key);
			if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, static::$special_header)) {
				if ($key === 'HTTP_CONTENT_LENGTH') {
					continue;
				}
				$env[$key] = $value;
			}
		}
        
        return $env;
    }
    
    public function getResourceUri(){
        return $this->env['PATH_INFO'];
    }
    
    /********************************************************************************
    * Routing Methods
    *******************************************************************************/
    
    protected function getPrePattern(){
        $prefix = trim($this->settings['prePattern'], '/');
        return $prefix ? '/' . $prefix : '';
    }
	
	protected function mapRoute($args){
        $pattern = $this->getPrePattern() . array_shift($args);
        $callable = array_pop($args);
        $route = new CoreRoute($pattern, $callable, $this->settings['routes.case_sensitive']);
        $this->router->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }
    
    public function map(){
        $args = func_get_args();
        return $this->mapRoute($args);
    }
	
	public function get(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_GET, self::METHOD_HEAD);
    }

    public function post(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_POST);
    }

    public function put(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_PUT);
    }

    public function patch(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_PATCH);
    }
	
    public function delete(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_DELETE);
    }
    
    public function options(){
        $args = func_get_args();
        return $this->mapRoute($args)->via(self::METHOD_OPTIONS);
    }
    
    public function any(){
        $args = func_get_args();
        return $this->mapRoute($args)->via("ANY");
    }
    
    public function group(){
        $args = func_get_args();
        $pattern = array_shift($args);
        $callable = array_pop($args);
        $this->router->pushGroup($pattern, $args);
        if (is_callable($callable)) {
            call_user_func($callable);
        }
        $this->router->popGroup();
    }
    
    /********************************************************************************
    * Request Methods
    *******************************************************************************/
    
    public function getMethod(){
        return $this->env['REQUEST_METHOD'];
    }
    
    public function isHead(){
        return $this->getMethod() === self::METHOD_HEAD;
    }
    
    public function params($key = null, $default = null){
        $union = array_merge($this->getRequest(), $this->postRequest());
        if ($key) {
            return isset($union[$key]) ? $union[$key] : $default;
        }

        return $union;
    }
    
    public function getRequest($key = null, $default = null){
        if (!isset($this->env['request.query_hash'])) {
            $output = array();
            if (function_exists('mb_parse_str')) {
                mb_parse_str($this->env['QUERY_STRING'], $output);
            } else {
                parse_str($this->env['QUERY_STRING'], $output);
            }
            $this->env['request.query_hash'] = Util::stripSlashesIfMagicQuotes($output);
        }
        if ($key) {
            if (isset($this->env['request.query_hash'][$key])) {
                return $this->env['request.query_hash'][$key];
            } else {
                return $default;
            }
        } else {
            return $this->env['request.query_hash'];
        }
    }
    
    public function postRequest($key = null, $default = null){
        if (!isset($this->env['request.form_hash'])) {
            $this->env['request.form_hash'] = Util::stripSlashesIfMagicQuotes($_POST);
        }
        if ($key) {
            if (isset($this->env['request.form_hash'][$key])) {
                return $this->env['request.form_hash'][$key];
            } else {
                return $default;
            }
        } else {
            return $this->env['request.form_hash'];
        }
    }
    
    public function putRequest($key = null, $default = null){
        return $this->postRequest($key, $default);
    }
    
    public function deleteRequest($key = null, $default = null){
        return $this->postRequest($key, $default);
    }
    
    public function patchRequest($key = null, $default = null){
        return $this->postRequest($key, $default);
    }
    
    public function cookies($key = null){
        if ($key) {
            return $this->cookies[$key];
        }
        return $this->cookies;
    }
    
    /********************************************************************************
    * Response Methods
    *******************************************************************************/
    
    public function setStatus($status){
        $this->status = (int)$status;
    }
    
    public function header($name, $value = null){
        if (!is_null($value)) {
            $this->headers[$name] = $value;
        }
        return $this->headers[$name];
    }
    
    public function contentType($type){
        $this->header('Content-Type', $type);
    }
    
    public function write($body, $replace = false){
        if ($replace) {
            $this->body = $body;
        } else {
            $this->body .= (string)$body;
        }
        $this->length = strlen($this->body);

        return $this->body;
    }
    
    public function redirect($url, $status = 302){
        $this->setStatus($status);
        $this->headers['Location'] = $url;
    }
    
    public function initialize($body = '', $status = 200, $headers = array('Content-Type' => 'text/html')){
        $this->setStatus($status);
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }
        $this->write($body);
    }
    
    public function finalize(){
        return array($this->status, $this->headers, $this->body);
    }
    
    public static function getMessageForCode($status){
        if (isset(self::$messages[$status])) {
            return self::$messages[$status];
        } else {
            return null;
        }
    }
    
    /********************************************************************************
    * Rendering Methods
    *******************************************************************************/
    
    public function render($template, $data = array(), $status = null){
        if (!is_null($status)) {
            $this->setStatus($status);
        }
        $this->view->appendData($data);
        $this->view->render($template);
    }
    
    public function prefetch($template, $data = array()){
        $this->view->appendData($data);
        return $this->view->prefetch($template);
    }
    
    /********************************************************************************
    * Run Methods
    *******************************************************************************/
    
    public function run(){
        $this->initialize();
        
        $this->call();
        
        list($status, $headers, $body) = $this->finalize();
        
        if (headers_sent() === false) {
            //Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', $this->getMessageForCode($status)));
            } else {
                header(sprintf('HTTP/%s %s', $this->settings['http.version'], $this->getMessageForCode($status)));
            }

            //Send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("$name: $hVal", false);
                }
            }
        }
        
        if (!$this->isHead()) {
            echo $body;
        }
    }
    
    public function call(){
        try{
            ob_start();
            $dispatched = false;
            $matchedRoutes = $this->router->getMatchedRoutes($this->getMethod(), $this->env['PATH_INFO']);
            foreach ($matchedRoutes as $route) {
                try {
                    $dispatched = $route->dispatch();
                    if ($dispatched) {
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            if (!$dispatched) {
                $this->notFound();
            }
            
            $this->write(ob_get_clean());
        }
        catch (Exception $e) {
        }
    }
    
    /********************************************************************************
    * Error Handling Methods
    *******************************************************************************/
	
	public function notFound ($callable = null){
        if (is_callable($callable)) {
            $this->notFound = $callable;
        } else {
            ob_start();
            if (is_callable($this->notFound)) {
                call_user_func($this->notFound);
            } else {
                call_user_func(array($this, 'defaultNotFound'));
            }
            $this->halt(404, ob_get_clean());
        }
    }
	
	protected static function generateTemplateMarkup($title, $body)
    {
        return sprintf("<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>", $title, $title, $body);
    }

    /**
     * Default Not Found handler
     */
    protected function defaultNotFound()
    {
        echo static::generateTemplateMarkup('404 Page Not Found', '<p>The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly. If all else fails, you can visit our home page at the link below.</p><a href="' . $this->env['SCRIPT_NAME'] . '/">Visit the Home Page</a>');
    }
    
    /********************************************************************************
    * Helper Methods
    *******************************************************************************/
    
    protected function cleanBuffer(){
        if (ob_get_level() !== 0) {
            ob_clean();
        }
    }
    
    public function halt($status, $message = ''){
        $this->cleanBuffer();
        $this->setStatus($status);
        $this->write($message);
    }
}
?>