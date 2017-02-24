<?php

class Util {
    public static $custom_time;
	
	public static function replace_tag($contents, $replacements = 'none') {
		$method_code = '0001';
		if (is_array($replacements)) {
			while (list($code, $replace) = each ($replacements)) {
				$contents = str_replace($code, $replace, $contents);
			}
		}
		return $contents;
	}
    
    public static function stripSlashesIfMagicQuotes($rawData, $overrideStripSlashes = null){
        $method_code = '0002';
        $strip = is_null($overrideStripSlashes) ? get_magic_quotes_gpc() : $overrideStripSlashes;
        if ($strip) {
            return self::stripSlashes($rawData);
        }

        return $rawData;
    }
    
    protected static function stripSlashes($rawData){
        $method_code = '0003';
        return is_array($rawData) ? array_map(array('self', 'stripSlashes'), $rawData) : stripslashes($rawData);
    }
    
    public static function system_datetime($format = '', $timezone_offset = null){
		$method_code = '0004';
		
		$date_format = $format? $format : "Y-m-d H:i:s";
		
		return date($date_format, Util::system_time($timezone_offset));
	}
    
    public static function system_time($timezone_offset = null){
		$method_code = '0005';
        global $vars;
		
		if($timezone_offset !== null){
			$local_offset = $timezone_offset;
		}
		else{
			$local_offset = round($vars['timezone_offset'] * 3600);
		}
		$server_offset = date("Z");
		if(static::$custom_time){
			$time = static::$custom_time;
		}
		else{
			$time = time();
		}
		$local_time = $time - $server_offset + $local_offset;
		
		return $local_time;
	}
    
    public static function format_image_filename($image_original_name){
		$method_code = '0006';
		
		$extension = static::get_file_extension($image_original_name);
        $md5File = md5($image_original_name);
        
		$image_formatted_name = $md5File . '_' . Util::system_time() . '.' . $extension;
        
		return $image_formatted_name;
	}
    
    public static function get_file_extension($file){
		$method_code = '0007';
		
		$extension = '';
		if(strstr($file, '.') !== false){
			$extension = strtolower(substr($file, strrpos($file, ".")+1));
		}
		
		return $extension;
	}
    
    public static function generateRandomCode($length=32, $type="mix"){
		$method_code = '0008';
		$num		= "1234567890";
		$alpha		= "abcdefghjkmnpqrstuvwxyz";
		$mix		= "$alpha$num";
		$src		= ($type=="mix"? $mix : ($type=="alpha"? $alpha : $num));
		$src_len 	= strlen($src);
		
		$random_code = '';
		for($i=0; $i<$length; $i++){
			$code = strval(substr($src, mt_rand(0, $src_len-1), 1));
			if(mt_rand(1,2) == 2){
				$code = strtoupper($code);
			}
			$random_code .= $code;
		}
		return strval($random_code);
	}
}