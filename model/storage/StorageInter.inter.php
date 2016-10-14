<?php
interface StorageInter{
	
	/**
	 * Upload multipart media
	 */
	public function save_standard_media($media, &$filename);
	
    /**
	 * Upload base64 media
	 */
	public function save_base64_media($media, &$filename);
}
?>