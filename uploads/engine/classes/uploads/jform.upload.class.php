<?php
/*
=====================================================
 DataLife Engine v14.0
-----------------------------------------------------
 Persian support site: https://dlefa.ir
-----------------------------------------------------
 FileName :  engine/classes/uploads/upload.class.php
-----------------------------------------------------
 Copyright (c) 2020, All rights reserved.
=====================================================
*/


if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

class JformFileUploader {

	private $name;
	private $form_id;
	private $allowed_extensions;	
	private $max_size;	

    function __construct($name,$form_id,$allowed_extensions=null,$max_size=null){        
		$this->name = $name;
		$this->form_id = $form_id;
		$this->allowed_extensions = $allowed_extensions;
		// size must be to KB(KiloBytes)
		$this->max_size = $max_size;
		define( 'FOLDER_PREFIX', "jform/{$this->form_id}/" );
    }

    function getFileName() {
		if (is_array($this->name['name'])){
			foreach ($this->name['name'] as $name) {
				$path_parts[] = @pathinfo($name)['basename'];
			}
			return $path_parts;
		}
		
		$path_parts = @pathinfo($this->name['name']);
		return $path_parts['basename'];
	}
	// Get filesize to KB
    function getFileSize() {
		if (is_array($this->name['size'])){
			foreach ($this->name['size'] as $item) {
				$file_sizes[] = $item/1024;
			}
			return $file_sizes;
		}
		
		return $this->name['size']/1024;
    }

	function clear_url_dir($var) {
		if ( is_array($var) ) return "";

		$var = str_replace(chr(0), '', $var);		
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = str_ireplace( ".phtm", ".pppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );

		return $var;
		
	}

    private function msg_error($message) {
		return ["error" => $message];
	}

	private function check_filename ( $filename ) {
		global $config;
		
		if( $filename != "" ) {

			$filename = str_replace( "\\", "/", $filename );
			$filename = preg_replace( '#[.]+#i', '.', $filename );
			$filename = str_replace( "/", "", $filename );
			$filename = str_ireplace( "php", "", $filename );

			$filename_arr = explode( ".", $filename );
			
			if(count($filename_arr) < 2) {
				return false;
			}
			
			$type = totranslit( end( $filename_arr ) );
			
			if(!$type) return false;
			
			$curr_key = key( $filename_arr );
			unset( $filename_arr[$curr_key] );
 
			$filename = totranslit( implode( "_", $filename_arr ) );
			
			if( !$filename ) {
				$filename = time() + rand( 1, 100 );
			}
			
			$filename = $filename . "." . $type;

		} else return false;

		$filename = preg_replace( '#[.]+#i', '.', $filename );

		if( stripos ( $filename, ".php" ) !== false ) return false;
		if( stripos ( $filename, ".phtm" ) !== false ) return false;
		if( stripos ( $filename, ".shtm" ) !== false ) return false;
		if( stripos ( $filename, ".htaccess" ) !== false ) return false;
		if( stripos ( $filename, ".cgi" ) !== false ) return false;
		if( stripos ( $filename, ".htm" ) !== false ) return false;
		if( stripos ( $filename, ".ini" ) !== false ) return false;

		if( stripos ( $filename, "." ) === 0 ) return false;
		if( stripos ( $filename, "." ) === false ) return false;
		
		if( dle_strlen( $filename, $config['charset'] ) > 170 ) {
			return false;
		}

		return $filename;

	}

	function saveFile($path, $filename, $prefix=true) {

		if ( $prefix ) {

			$file_prefix = time() + rand( 1, 100 );
			$file_prefix .= "_";

		} else $file_prefix = "";		

		
		if (is_array($this->name['tmp_name'])) {
			$i = 0;
			foreach ($this->name['tmp_name'] as $temp_name) {
				$temp_upload_filename = totranslit( $file_prefix.$filename[$i] );
				if(!move_uploaded_file($temp_name, $path.$temp_upload_filename)){
					return false;
				}
				$upload_filename[] = $temp_upload_filename;
				$i++;
			}
		} else {
			$upload_filename = totranslit( $file_prefix.$filename );
			if(!@move_uploaded_file($this->name['tmp_name'], $path.$upload_filename)){
				return false;
			}
		}

        return $upload_filename;
	}
	
	/**
	 * if upload succeeds, function will retuen string file name
	 * otherwise it will return an array with error key and error message value
	 */
	function FileUpload(){
		global $lang;

		if( !is_dir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {

			mkdir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX, 0777, true );
			chmod( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX, 0777 );

		}
		if( !is_dir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( "پوشه یافت نشد: "." /uploads/files/" . FOLDER_PREFIX, 403 );
		}
		if( !is_writable( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( "پوشه قابل نوشتن نیست: "." /uploads/files/" . FOLDER_PREFIX . " ".$lang['upload_error_2'], 403 );
		}

		if (is_array($this->getFileName())){
			foreach ($this->getFileName() as $fname) {
				$tname = $this->check_filename( $fname );
				if (!$tname){
					return $this->msg_error( "مشکلی در نام فایل ارسالی وجود دارد." );
				}
				$filename[] = $tname;
			}

		} else {
			$filename = $this->check_filename( $this->getFileName() );
	
			if (!$filename){
				return $this->msg_error( "مشکلی در نام فایل ارسالی وجود دارد." );
			}
		}
		
		if (is_array($filename)) {
			foreach ($filename as $temp) {
				$filename_arr = explode( ".", $temp );
				$type = end( $filename_arr );
				if (!$type){
					return $this->msg_error( "مشکلی در پسوند فایل ارسالی وجود دارد." );
				}
				if( $this->allowed_extensions != null AND !in_array($type, $this->allowed_extensions ) ) {
					return $this->msg_error( "ارسال این نوع فایل مجاز نیست." );
				}				
			}
		} else {
			$filename_arr = explode( ".", $filename );
			$type = end( $filename_arr );
			if (!$type){
				return $this->msg_error( "مشکلی در پسوند فایل ارسالی وجود دارد." );
			}
			if( $this->allowed_extensions != null AND !in_array($type, $this->allowed_extensions ) ) {
				return $this->msg_error( "ارسال این نوع فایل مجاز نیست." );
			}
		}
		
		$size = $this->getFileSize();
		if (is_array($size)) {
			foreach ($size as $temp_size) {
				if (!$temp_size) {
					return $this->msg_error( "مشکلی در اندازه فایل ارسالی وجود دارد" );
				}
				if( $this->max_size != null AND intval($temp_size) > intval($this->max_size) ) {
					return $this->msg_error( "اندازه فایل ارسالی بزرگتر از حد مجاز است." );
				}
			}
		} else{			
			if (!$size) {
				return $this->msg_error( "مشکلی در اندازه فایل ارسالی وجود دارد" );
			}
			if( $this->max_size != null AND intval($temp_size) > intval($this->max_size) ) {
				return $this->msg_error( "اندازه فایل ارسالی بزرگتر از حد مجاز است." );
			}			
		}

		$uploaded_filename = $this->saveFile(ROOT_DIR . "/uploads/files/".FOLDER_PREFIX, $filename, true);
		if (is_array($uploaded_filename) AND count($uploaded_filename) > 0) {
			foreach ($uploaded_filename as $file) {
				@chmod( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX . $file, 0666 );
			}
			return implode(',', $uploaded_filename);
		}
		if ( $uploaded_filename ) {
			@chmod( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX . $uploaded_filename, 0666 );
			return $uploaded_filename;
		} else return $this->msg_error( "مشکلی در آپلود فایل به وجود آمده است." );
		

	}

}

?>