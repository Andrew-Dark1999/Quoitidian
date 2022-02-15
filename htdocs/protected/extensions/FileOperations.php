<?php
/**
 * @author Alex R.
 * @copyright 2014
 */

class FileOperations{

    private $mimes = array(	'hqx'	=>	'application/mac-binhex40',
            				'cpt'	=>	'application/mac-compactpro',
            				'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
            				'bin'	=>	'application/macbinary',
            				'dms'	=>	'application/octet-stream',
            				'lha'	=>	'application/octet-stream',
            				'lzh'	=>	'application/octet-stream',
            				'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
            				'class'	=>	'application/octet-stream',
            				'psd'	=>	'application/x-photoshop',
            				'so'	=>	'application/octet-stream',
            				'sea'	=>	'application/octet-stream',
            				'dll'	=>	'application/octet-stream',
            				'oda'	=>	'application/oda',
            				'pdf'	=>	array('application/pdf', 'application/x-download'),
            				'ai'	=>	'application/postscript',
            				'eps'	=>	'application/postscript',
            				'ps'	=>	'application/postscript',
            				'smi'	=>	'application/smil',
            				'smil'	=>	'application/smil',
            				'mif'	=>	'application/vnd.mif',
            				'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
            				'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
            				'wbxml'	=>	'application/wbxml',
            				'wmlc'	=>	'application/wmlc',
            				'dcr'	=>	'application/x-director',
            				'dir'	=>	'application/x-director',
            				'dxr'	=>	'application/x-director',
            				'dvi'	=>	'application/x-dvi',
            				'gtar'	=>	'application/x-gtar',
            				'gz'	=>	'application/x-gzip',
            				'php'	=>	'application/x-httpd-php',
            				'php4'	=>	'application/x-httpd-php',
            				'php3'	=>	'application/x-httpd-php',
            				'phtml'	=>	'application/x-httpd-php',
            				'phps'	=>	'application/x-httpd-php-source',
            				'js'	=>	'application/x-javascript',
            				'swf'	=>	'application/x-shockwave-flash',
            				'sit'	=>	'application/x-stuffit',
            				'tar'	=>	'application/x-tar',
            				'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
            				'xhtml'	=>	'application/xhtml+xml',
            				'xht'	=>	'application/xhtml+xml',
            				'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
            				'mid'	=>	'audio/midi',
            				'midi'	=>	'audio/midi',
            				'mpga'	=>	'audio/mpeg',
            				'mp2'	=>	'audio/mpeg',
            				'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            				'aif'	=>	'audio/x-aiff',
            				'aiff'	=>	'audio/x-aiff',
            				'aifc'	=>	'audio/x-aiff',
            				'ram'	=>	'audio/x-pn-realaudio',
            				'rm'	=>	'audio/x-pn-realaudio',
            				'rpm'	=>	'audio/x-pn-realaudio-plugin',
            				'ra'	=>	'audio/x-realaudio',
            				'rv'	=>	'video/vnd.rn-realvideo',
            				'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
            				'bmp'	=>	array('image/bmp', 'image/x-windows-bmp'),
            				'gif'	=>	'image/gif',
            				'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
            				'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
            				'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
            				'png'	=>	array('image/png',  'image/x-png'),
            				'tiff'	=>	'image/tiff',
            				'tif'	=>	'image/tiff',
            				'css'	=>	'text/css',
            				'html'	=>	'text/html',
            				'htm'	=>	'text/html',
            				'shtml'	=>	'text/html',
            				'txt'	=>	'text/plain',
            				'text'	=>	'text/plain',
            				'log'	=>	array('text/plain', 'text/x-log'),
            				'rtx'	=>	'text/richtext',
            				'rtf'	=>	'text/rtf',
            				'xml'	=>	'text/xml',
            				'xsl'	=>	'text/xml',
            				'mpeg'	=>	'video/mpeg',
            				'mpg'	=>	'video/mpeg',
            				'mpe'	=>	'video/mpeg',
            				'qt'	=>	'video/quicktime',
            				'mov'	=>	'video/quicktime',
            				'avi'	=>	'video/x-msvideo',
            				'movie'	=>	'video/x-sgi-movie',
            				'doc'	=>	'application/msword',
            				'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            				'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'),
            				'word'	=>	array('application/msword', 'application/octet-stream'),
            				'xl'	=>	'application/excel',
            				'eml'	=>	'message/rfc822',
            				'json' => array('application/json', 'text/json')
			);




    public static function getInstance(){
        return new self;
    } 
    
    

    /**
    * Возвращает тип файла по его mime-типу
    */    
    public function getFileType($file_mime, $mimes = null){
        if($mimes === null) $mimes = $this->mimes;
        foreach($mimes as $key => $value){
            if(is_array($value)){
                $result = $this->getFileType($file_mime, $value);
                if(is_numeric($result)) return $key;
            }
            
            if($value == $file_mime) return $key;
        }
        return substr($file_mime, strpos($file_mime, '/')+1, strpos($file_mime, ';') - strpos($file_mime, '/')-1);
    }


    /**
    * Возвращает его mime-типу по типу файла 
    */    
    public function getFileMime($file_type){
        return implode(',', $this->mimes[$file_type]);
    }




	public function downloadFromStream($absract_filename = '', $data = '')
	{
		if ($absract_filename == '' OR $data == ''){
			return FALSE;
		}
		// Try to determine if the filename includes a file extension.
		// We need it in order to set the MIME type
		if (FALSE === strpos($absract_filename, '.')){
			return FALSE;
		}

		// Grab the file extension
		$x = explode('.', $absract_filename);
		$extension = end($x);

		// Set a default mime if we can't find it
		if ( ! isset($this->mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($this->mimes[$extension])) ? $this->mimes[$extension][0] : $this->mimes[$extension];
		}

		// Generate the server headers
		if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
		{
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$absract_filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".strlen($data));
		}
		else
		{
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$absract_filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".strlen($data));
		}
        return $data;
	}    
    




    /**
    *   возвращает массив ключей связаных файлов
    */ 
    public function getKeysByField($table_name, $field_name){
        $data_array = array();
        try{
            $data_model = DataModel::getInstance()
                                ->setSelect($field_name)
                                ->setFrom('{{' . $table_name . '}}')
                                ->setWhere($field_name . '!="" AND ' . $field_name . ' is not NULL')
                                ->findAll();
            if(!empty($data_model)){
                foreach($data_model as $value)
                    $data_array[] = $value[$field_name]; 
            }
            return $data_array;
        } catch(Exception $e){
            return $data_array;
        }
    }



    /**
    *   Удаляем все файлы из предложеного поля модуля 
    */ 
    public function deleteAllFilesByField($relate_keys){
        try{
            if(empty($relate_keys)) return false;
            $criteria = new CDbCriteria;
            $criteria->addInCondition('relate_key', $relate_keys);
           
            UploadsModel::model()->updateAll(array('status'=>'to_remove'), $criteria);
    
            $uploads = UploadsModel::model()->findAll('status = "to_remove"');
            if(!empty($uploads)){
                foreach($uploads as $upload){
                    $upload->delete();
                }
            }
        } catch(Exception $e){}
    }



  
    
    /**
    *   Удаляем все загруженые файлы заданого модуля
    */
    public function deleteAllFilesByTableModel($table_model){
        $table_name = $table_model->extensionCopy()->getTableName(null, false);
        $schema = $table_model->extensionCopy()->getSchemaParse();
        if(!isset($schema['elements'])) return;
        foreach($schema['elements'] as $value){
            if(isset($value['field']) &&
              ($value['field']['params']['type'] == 'file' ||
               $value['field']['params']['type'] == 'file_image' ||
               $value['field']['params']['type'] == 'attachments' ||
               $value['field']['params']['name'] == 'ehc_image1'))
                    $this->deleteAllFilesByField($this->getKeysByField($table_name, $value['field']['params']['name']));
        }
    }
    



	public function removeDirectory($dir) {
		if ($objs = glob($dir . "/*")) {
			foreach($objs as $obj) {
				is_dir($obj) ? FileOperations::getInstance()->removeDirectory($obj) : @unlink($obj);
			}
		}
		@rmdir($dir);
	}




	public static function getTempFileName($prefix = 'tmp'){
        $path = \YiiBase::getPathOfAlias('webroot') . '/' . \ParamsModel::getValueFromModel('upload_path_tmp');

        if(is_dir($path) == false){
        	$m = mkdir($path, 0777, true);
        	if($m === false) return false;
		}

        $tmpfname = tempnam($path, $prefix);

        return $tmpfname;
    }


    /**
	 * Возвращает полный путь к переопределенному файлу для компании.
	 * Если файл не переопределен, возвращается $real_path_name . \ . $file_name
	 * По умолчанию файл ищется в папке /static/redefined/

	 * @param string $file_name - Название файла
	 * @param string $real_path_name - путь к файлу по умолчанию. Не должен начинатся с "/static", содержать открывающий и закрывающий слеш.
	 *
	 * @return string
     */
    public static function getRedefinedFile($file_name, $real_path_name = null){
    	$ds = DIRECTORY_SEPARATOR;
		$redefined_path_name = $ds . 'static' . $ds . 'redefined' . $ds . $real_path_name;
		$default_path_name = $ds . 'static' . $ds . $real_path_name;

		// ищем в company
		if(file_exists(Yii::app()->basePath . $ds . '..' . $redefined_path_name . $ds . $file_name)){
			return $redefined_path_name . $ds . $file_name;
        } else {
            // возвращаем то, что указано по умолчанию для СРМ
			if($default_path_name){
                return $default_path_name . $ds . $file_name;
			}
		}

    	if($file_name){
            return $file_name;
		}
	}




    /**
	 * isImageType - воззвращает статус, является ли файл изображением
     * @param $mime_type
     * @return bool
     */
    public static function isImageType($mime_type){
		if(!$mime_type) return false;

		$image_mime_types = [
            "image/jpeg",
			"image/pjpeg",
			"image/png",
			"image/x-png",
			"image/gif",
            "image/bmp",
			"image/x-ms-bmp",
			"image/x-windows-bmp",
			"image/tiff",
		];

		if(in_array($mime_type, $image_mime_types)){
			return true;
		}

		return false;
	}


    
}



?>
