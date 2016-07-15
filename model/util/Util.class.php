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
}