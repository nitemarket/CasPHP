<?php

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class AwsS3Storage extends Storage{
	protected $_class_code = 2401;
    
    protected $_media_path;
    protected $_bucket_URL;
    private $_s3;
    
    private static $_storage_class = array(
        'STANDARD',
        'STANDARD_IA',
        'GLACIER',
        'REDUCED_REDUNDANCY',
    );
	
	public function __construct($bucket){
		$method_code = 'A001';
        
        $this->_media_path = $bucket;
        $this->_bucket_URL = 'https://' . $bucket . '.s3.amazonaws.com';
        
        parent::__construct();
        
        $options = array(
            'version' => '2006-03-01',
            'region' => 'us-west-2',
            'http'    => [
                'verify' => false
            ]
        );
        
        if(System::is_env('live')){
            $options['credentials'] = array(
                'key'    => System::$vars['aws']['s3']['aws_access_key_id'],
                'secret' => System::$vars['aws']['s3']['aws_secret_access_key'],
            );
        }
        else{
            $options['profile'] = System::$vars['aws']['s3']['profile'];
        }
        
        // Instantiate the client.
        $this->_s3 = S3Client::factory($options);
	}
    
    public function get_object_url($filename){
        $method_code = 'A002';
        
        $media_key = $this->_get_media_subdirectory($filename) . '/' . $filename;
        return $this->_s3->getObjectUrl($this->_media_path, $media_key);
    }
    
    private function _get_storage_class($class = 'STANDARD'){
        $method_code = 'A003';
        
        if(!in_array($class, static::$_storage_class)){
            $log = "Invalid storage class";
            $error_code = $method_code.'001';
        	throw new Xception($log, $error_code);
        }
        
        return $class;
    }
	
    public function save_standard_media($media, &$filename){
        $method_code = 'A100';
        
        $filename = $this->_format_media_filename($filename);
        $media_key = $this->_get_media_subdirectory($filename) . '/' . $filename;
        
        try{
            $result = $this->_s3->putObject(array(
                'Bucket'       => $this->_media_path,
                'Key'          => $media_key,
                'SourceFile'   => $media['tmp_name'],
                'ACL'          => 'public-read',
                'StorageClass' => $this->_get_storage_class(),
            ));
        }
        catch(S3Exception $e){
            $log = "Error saving media in AWS S3 storage. Error: " . $e->getMessage();
            $error_code = $method_code.'001';
        	throw new Xception($log, $error_code);
        }
        
        return $result['ObjectURL'];
    }
    
    public function save_base64_media($media, &$filename = ''){
        $method_code = 'A101';
        
        //validation
        preg_match('#data:([^;]+);base64,(.+)#', $media, $matches);
        if(count($matches) <= 0){
            $log = "Invalid base64 media";
            $error_code = $method_code.'001';
        	throw new Xception($log, $error_code);
        }
        
        //process
        $info_data = explode('/', $matches[1]);
        $media_type = $info_data[0];
        $extension = $info_data[1];
        $filename = Util::generateRandomCode(10) . '.' . $extension;
        
        $media_data = array(
            'content-type' => $matches[1],
            'type' => $media_type,
            'extension' => $extension,
            'filename' => $filename,
            'decoded_image' => base64_decode($matches[2]),
        );
        
        //store
        $filename = $this->_format_media_filename($media_data['filename']);
        $media_key = $this->_get_media_subdirectory($filename) . '/' . $filename;
        
        try{
            $result = $this->_s3->putObject(array(
                'Bucket'        => $this->_media_path,
                'Key'           => $media_key,
                'Body'          => $media_data['decoded_image'],
                'ContentType'   => $media_data['content-type'],
                'ACL'           => 'public-read',
                'StorageClass' => $this->_get_storage_class(),
            ));
        }
        catch(S3Exception $e){
            $log = "Error saving media in AWS S3 storage. Error: " . $e->getMessage();
            $error_code = $method_code.'001';
        	throw new Xception($log, $error_code);
        }
        
        return $result['ObjectURL'];
    }
}
?>