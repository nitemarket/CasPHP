<?php

abstract class Storage implements StorageInter{
	protected $_class_code = 2400;
    
    protected $_media_path;
	
	public function __construct(){
		$method_code = '0001';
		
		$this->_basic_validation();
	}
    
    private function _basic_validation(){
        $method_code = '0002';
        
        if(!$this->_media_path){
            $log = "Invalid media path for storage";
            $error_code = $method_code.'001';
        	throw new Exception($log, $error_code);
        }
    }
	
    protected function _format_media_filename($filename){
        $method_code = '0003';
        
        return Util::format_image_filename($filename);
    }
    
    protected function _get_media_subdirectory($formatted_filename){
        $method_code = '0004';
        
        return $formatted_filename[0] . '/' . $formatted_filename[1].$formatted_filename[2];
    }
    
    public function get_media_directory($formatted_filename){
        $method_code = '0005';
        
        return $this->_media_path . '/' . $this->_get_media_subdirectory($formatted_filename);
    }
}
?>