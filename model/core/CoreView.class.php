<?php
/**
 * CasPHP - a PHP 5 framework
 *
 * @author      Cas Chan <casper_ccb@hotmail.com>
 * @version     1.0.0
 * @credit      Slim Framework
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

class CoreView {
    protected $data = array();
    
	protected $default_template = array('404.html');
    protected $templatesDirectory;
    protected $includeTagPattern = '/<%%([\w\-\_]+)\.(\w+)?%%>/';
    
    protected $javascriptSRCTags = array();
    protected $javascriptTextTags = array();
    protected $cssSRCTags = array();
	
	public function __construct(){
		$this->templatesDirectory = TEMPLATE_PATH;
        $this->includeURL = TEMPLATE_URL . TEMPLATE_ASSET;
		
	}
    
    /********************************************************************************
    * Data Methods
    *******************************************************************************/
	
    public function setData(){
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0])) {
            foreach($args[0] as $var => $val){
                $this->data[$var] = $val;
            }
        } elseif (count($args) === 2) {
            $this->data[$args[0]] = $args[1];
        } else {
            throw new InvalidArgumentException('Cannot set View data with provided arguments. Usage: `View::setData( $key, $value );` or `View::setData([ key => value, ... ]);`');
        }
    }
    
    public function appendData($data){
        if (!is_array($data)) {
            throw new InvalidArgumentException('Cannot append view data. Expected array argument.');
        }
        foreach($data as $var => $val){
            $this->data[$var] = $val;
        }
    }
    
    /********************************************************************************
    * Process Template Tag Methods
    *******************************************************************************/
    
    protected function replace_template_tag($matches){
        $folders = false;
        $file = $matches[1];
        if(strpos($matches[1], '_') !== false){
            $path_tag = explode('_', $matches[1]);
            
            $file = array_pop($path_tag);
            $folders = implode('/', $path_tag);
        }
        
        $includeTemplatePathname = $this->getIncludeTemplatePathname($file, $folders, $matches[2]);
        if(file_exists($includeTemplatePathname)){
            extract($this->data, EXTR_PREFIX_ALL, '_');
            ob_start();
            require $includeTemplatePathname;
            $template = ob_get_clean();
            
            return $this->extractIncludeTemplate($template);
        }
        
        return $matches[0];
    }
    
    public function getIncludeTemplatePathname($file, $folder = "", $extension = ""){
        return $this->templatesDirectory . '/' . ($folder ? (trim($folder, '/') . "/") : "") . trim($file, '/') . ($extension ? ("." . $extension) : "");
    }
    
    public function getTemplatePathname($file){
        $templateFile = $this->templatesDirectory . '/' . ltrim($file, '/');
        if(!file_exists($templateFile)){
            throw new Exception;
        }
        return $templateFile;
    }
    
    protected function extractIncludeTemplate($template){
        $template = preg_replace_callback($this->includeTagPattern, array($this, 'replace_template_tag'), $template);
        return $template;
    }
    
    protected function processGeneralTag($template){
        $template = preg_replace('/<%include_path%>/', $this->includeURL, $template);
        return $template; 
    }
    
    /********************************************************************************
    * Reposition CSS & Javascript Methods
    *******************************************************************************/
    
    protected function replace_javascript_tag($matches){
        if($matches[2]){
            $this->javascriptTextTags[] = $matches[0]; //text
        }
        else{
            if(!in_array($matches[0], $this->javascriptSRCTags)){
                $this->javascriptSRCTags[] = $matches[0]; //src
            }
        }
        return '';
    }
    
    protected function processJavascriptTag($template){
        if (preg_match("/<%javascript_tag%>/", $template)) {
            $template = preg_replace_callback('/<script((?!stick|>).)*>(((?!<\/).)*)<\/script>/is', array($this, 'replace_javascript_tag'), $template);
        }
        $template = preg_replace('/<%javascript_tag%>/', implode('', $this->javascriptSRCTags) . implode('', $this->javascriptTextTags), $template);
        return $template; 
    }
    
    protected function replace_css_tag($matches){
        $this->cssSRCTags[] = $matches[0]; //text
        return '';
    }
    
    protected function processCssTag($template){
        if (preg_match("/<%css_tag%>/", $template)) {
            $template = preg_replace_callback('/<(link|style)(?=[^<>]*?(?:type="(text\/css)"|>))(?=[^<>]*?(?:media="([^<>"]*)"|>))(?=[^<>]*?(?:href="(.*?)"|>))(?=[^<>]*(?:rel="([^<>"]*)"|>))(?:.*?<\/\1>|[^<>]*>)/is', array($this, 'replace_css_tag'), $template);
        }
        $template = preg_replace('/<%css_tag%>/', implode('', $this->cssSRCTags), $template);
        return $template; 
    }
    
    /********************************************************************************
    * Rendering Methods
    *******************************************************************************/
    
    public function render($template){
        $template = $this->getMainTemplate($template);
        $template = $this->extractIncludeTemplate($template);
        $template = $this->processGeneralTag($template);
        $template = $this->processCssTag($template);
//        $template = $this->processJavascriptTag($template);
        
        echo $template;
    }
    
    public function prefetch($template){
        $template = $this->getMainTemplate($template);
        return $template;
    }
	
	public function getMainTemplate($template){
		$templatePathname = $this->getTemplatePathname($template);
        if (!file_exists($templatePathname)) {
            throw new RuntimeException("View cannot render `$template` because the template does not exist. Path: ". $templatePathname);
        }
        
        extract($this->data, EXTR_PREFIX_ALL, '_');
        ob_start();
        require $templatePathname;
        return ob_get_clean();
	}
}
?>