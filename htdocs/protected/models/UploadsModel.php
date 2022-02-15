<?php

class UploadsModel extends CActiveRecord
{

    const SOURCE_MODULE = 'module';
    const SOURCE_GOOGLE_DOC = 'google_doc';
    const SOURCE_COMMUNICATIONS = 'communications';

    const SCENARIO_EMAIL_COPY_TO = 'email_copy_to';
    const SCENARIO_EMAIL_MOVE_TO = 'email_move_to';
    const SCENARIO_MODULE_COPY_TO = 'module_copy_to';
    const SCENARIO_MODULE_MOVE_TO = 'module_move_to';

    /*
    const ORIENTATION_ALBUM     = 'album';
    const ORIENTATION_BOOK      = 'book';
    */

    // путь к родительскому файлу при копировании
    public $file_path_copy = '';

    // старое назние файла (при копировании...)
    private $file_name_old;

    // указывает на существование мниатюр
    public $thumbs = '0';

    //тип файла
    private $file_type;

    //путь на диску для сохранения файлов модулей
    private static $_upload_path_module;

    // указывает сценарий для миниатюр
    // upload, copy, avatar, profile
    private $thumb_scenario = null;

    //дополнительная обработка изображений
    private $add_update_image = false;

    //формат файла. Значение приходит из фронта и используется в валидации
    //Если = null - не проверяется
    private $format;

    /**
     * размер изобрадения в пикселя. Значение приходит из фронта и используется в валидации
     *
     * @var array
     */
    private $image_size_pixels;

    // список названий файлов, включая путь к директории
    private static $temp_file_name_list = [];

    public $type_class = [
        'file_image'       => ['bmp', 'gif', 'jpg', 'jpeg', 'png', 'tga', 'tif', 'ico'],
        'file_application' => ['psd', 'pdf'],
        'file_text'        => ['doc', 'docx', 'rtf', 'txt', 'otf', 'odt', 'fb2'],
        'file_table'       => ['xls', 'xlsx', 'xlc', 'xlm', 'xlw', 'xlt'],
        'file_media'       => ['mp2', 'mp3', 'mp4', 'mov', 'wma', 'wav', 'wmv', 'avi', 'ai', 'cdr', 'eps', 'swg', 'fla', 'flw', 'swf'],
        'file_other'       => '',
        'file_google_doc'  => 'GDoc',
    ];

    public $file_thumbs = [
        30  => [
            'width'            => 45,
            'width_measure'    => '',
            'height'           => 30,
            'height_measure'   => 'px',
            'title'            => '30px',
            'constructor_show' => true,
            'scenario'         => ['avatar', 'profile', 'upload', 'activity', 'attachments', 'copy'],
            'proportional'     => false,
            'crop'             => true,
        ],
        32  => [
            'width'            => 32,
            'width_measure'    => '',
            'height'           => 32,
            'height_measure'   => 'px',
            'title'            => '32px',
            'constructor_show' => false,
            'scenario'         => ['avatar', 'profile', 'copy'],
            'proportional'     => true,
            'crop'             => false,
        ],
        42  => [
            'width'            => 42,
            'width_measure'    => '',
            'height'           => 42,
            'height_measure'   => 'px',
            'title'            => '42px',
            'constructor_show' => false,
            'scenario'         => ['avatar', 'profile', 'copy'],
            'proportional'     => true,
            'crop'             => false,
        ],
        60  => [
            'width'            => 90,
            'width_measure'    => '',
            'height'           => 60,
            'height_measure'   => 'px',
            'title'            => '60px',
            'constructor_show' => true,
            'scenario'         => ['avatar', 'upload', 'profile', 'activity', 'attachments', 'copy'],
            'proportional'     => false,
            'crop'             => true,
        ],
        85  => [
            'width'            => 85,
            'width_measure'    => '',
            'height'           => 85,
            'height_measure'   => 'px',
            'title'            => '85px',
            'constructor_show' => false,
            'scenario'         => ['avatar', 'profile', 'copy'],
            'proportional'     => true,
            'crop'             => false,
        ],
        140 => [
            'width'            => 140,
            'width_measure'    => '',
            'height'           => 140,
            'height_measure'   => 'px',
            'title'            => '140px',
            'constructor_show' => false,
            'scenario'         => ['avatar', 'profile', 'copy'],
            'proportional'     => true,
            'crop'             => false,
        ],
    ];

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{uploads}}';
    }

    public function rules()
    {
        return [
            ['file_name', 'file', 'maxSize' => HelperIniParams::getUploadMaxFileSize(), 'on' => 'upload'],
            ['file_name', 'validateFormat', 'on' => 'upload'],
            ['file_name', 'validateImageSizePixels', 'on' => 'upload'],
            ['relate_key,file_source,file_path,file_date_upload,date_create,user_create,user_edit,status,thumbs,copy_id', 'safe', 'on' => 'upload, copy, asserted'],
        ];
    }

    public function getStatus()
    {
        return $this->hasErrors() ? false : true;
    }

    private function getErrorMessageList()
    {
        if (!$this->hasErrors()) {
            return [];
        }

        $messages = [];

        foreach ($this->getErrors() as $attribute => $errors) {
            if (is_array($errors)) {
                $messages[$attribute] = $errors[0];
            } else {
                $messages[$attribute] = $errors;
            }
        }

        return $messages;

    }

    public function getResult()
    {
        return [
            'status'   => $this->getStatus(),
            'messages' => $this->getErrorMessageList(),
        ];
    }

    public function setFormat($format)
    {
        if (!$format) {
            return $this;
        }

        $this->format = explode(',', $format);

        return $this;
    }

    public function setImageSizePixels($image_size_pixels)
    {
        if (!$image_size_pixels) {
            return $this;
        }

        $this->image_size_pixels = explode(',', $image_size_pixels);

        return $this;
    }

    /**
     * Добавляет временный файл в список
     */
    private static function addTempFileNameList($temp_file_name_list)
    {
        self::$temp_file_name_list[] = $temp_file_name_list;
    }

    /**
     * Удаляет все временные файлы из списка, очищает список
     */
    public static function flushTempFiles()
    {
        if (empty(self::$temp_file_name_list)) {
            return;
        }

        foreach (self::$temp_file_name_list as $full_file_name) {
            unlink($full_file_name);
        }

        self::$temp_file_name_list = [];
    }

    public function setRelateKey($relate_key, $status = 'asserted')
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'relate_key=:relate_key AND status=:status',
            'params'    => [
                ':relate_key' => $relate_key,
                ':status'     => $status,
            ],
        ]);

        return $this;
    }

    public function setThumbScenario($scenario_name)
    {
        $this->thumb_scenario = $scenario_name;

        return $this;
    }

    public function setFileType($file_type)
    {
        $this->file_type = $file_type;

        return $this;
    }

    private function initFileType($full_file_name)
    {
        $mime_type = mime_content_type($full_file_name);

        if (FileOperations::isImageType($mime_type)) {
            $this->file_type = 'file_image';
        }

        return $this;
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     * Проверяет формат изображения
     *
     * @param $attribute
     * @param $value
     */
    public function validateFormat($attribute, $value)
    {
        if (!$this->format) {
            return;
        }

        $type = $this->getFileType();

        if (!in_array($type, $this->format)) {
            $this->addError($attribute, Yii::t('messages', 'Invalid file format "{s}"', ['{s}' => $type]));
        }
    }

    /**
     * Проверяет размер изображения в пикселях
     *
     * @param $attribute
     * @param $value
     */
    public function validateImageSizePixels($attribute, $value)
    {
        if (!$this->image_size_pixels) {
            return;
        }

        $info = $this->getImageSize();
        if (!$info) {
            return;
        }

        if ($info[0] > $this->image_size_pixels[0] || $info[1] > $this->image_size_pixels[1]) {
            $this->addError($attribute, Yii::t('messages', 'Image size should be no more than {s1}px. Received {s2}px', [
                '{s1}' => $this->image_size_pixels[0] . '*' . $this->image_size_pixels[1],
                '{s2}' => $info[0] . '*' . $info[1],
            ]));
        }
    }

    /**
     * @return array|bool
     */
    public function getImageSize()
    {
        if (!in_array($this->getFileType(), $this->type_class['file_image'])) {
            return;
        }

        return getimagesize($this->file_name->getTempName());
    }

    protected function beforeValidate()
    {
        if ($this->isNewRecord) {
            if (in_array($this->file_source, [self::SOURCE_MODULE])) {
                $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $this->file_name;
                $this->file_path = md5($relate_key);
            }
            $this->date_create = new CDbExpression('now()');
            $this->file_date_upload = new CDbExpression('now()');
            $this->user_create = WebUser::getUserId();

        } else {
            $this->user_edit = WebUser::getUserId();
            if ($this->scenario == 'upload') {
                $this->file_name_old = $this->file_name;
            }
        }

        if (in_array($this->scenario, ['upload'])) {
            $this->file_name = CUploadedFile::getInstanceByName('file');
            $this->file_title = $this->getFileName();
        }

        return true;
    }

    protected function beforeSave()
    {
        if ($this->isRealFile()) {
            $path_module = $this->getFilePath(true);

            if (!file_exists($path_module . '/' . $this->file_path)) {
                if (!mkdir($path_module . '/' . $this->file_path, 0755)) {
                    $this->addError($this->file_title, Yii::t('messages', 'Error creating directory'));

                    return false;
                }
            }

            if ($this->scenario == 'upload') {
                $file_name = Translit::forFileName($this->getFileName());
                if ($file_name === '') {
                    $this->addError($this->file_title, Yii::t('messages', 'Error file name transliteration in Latin script. There are invalid characters'));
                }

                if (!$this->file_name->saveAs($path_module . '/' . $this->file_path . '/' . $file_name)) {
                    $this->addError($this->file_title, Yii::t('messages', 'Error saving file'));

                    return false;
                } else {
                    $file_type = $this->getFileType($file_name);
                    if (in_array($file_type, ['gif', 'jpg', 'jpeg', 'png']) && $this->add_update_image) {
                        $this->updateThumbImage($path_module . '/' . $this->file_path . '/', $file_name, $this->add_update_image);
                    }
                }
                $this->file_name = $file_name;

                $this->createThumbs($path_module . '/' . $this->file_path . '/', $file_name);
            }

            if ($this->scenario == self::SCENARIO_MODULE_MOVE_TO) {
                $file_name = Translit::forFileName($this->getFileName());

                if ($file_name === '') {
                    $this->addError($this->file_title, Yii::t('messages', 'Error file name transliteration in Latin script. There are invalid characters'));
                }

                $destenation = $path_module . DIRECTORY_SEPARATOR . $this->file_path . DIRECTORY_SEPARATOR . $file_name;
                if (!file_exists($this->file_path_copy)) {
                    $this->addError($this->file_title, Yii::t('messages', 'File not found'));

                    return false;
                }
                if (!rename($this->file_path_copy, $destenation)) {
                    $this->addError($this->file_title, Yii::t('messages', 'Error saving file'));

                    return false;
                } else {
                    $file_type = $this->getFileType($file_name);
                    if (in_array($file_type, ['gif', 'jpg', 'jpeg', 'png']) && $this->add_update_image) {
                        $this->updateThumbImage($path_module . '/' . $this->file_path . '/', $file_name, $this->add_update_image);
                    }
                }
                $this->file_name = $file_name;

                $this->createThumbs($path_module . '/' . $this->file_path . '/', $file_name);
            }

            if ($this->scenario == 'copy') {
                copy($path_module . '/' . $this->file_path_copy . '/' . $this->getFileName(),
                    $path_module . '/' . $this->file_path . '/' . $this->getFileName());

                foreach ($this->file_thumbs as $key => $value) {
                    $prefix = $value['title'] . '_';
                    if (file_exists($path_module . '/' . $this->file_path_copy . '/' . $prefix . $this->getFileName())) {
                        copy($path_module . '/' . $this->file_path_copy . '/' . $prefix . $this->getFileName(),
                            $path_module . '/' . $this->file_path . '/' . $prefix . $this->getFileName());
                    }
                }
            }

            if (in_array($this->scenario, [self::SCENARIO_EMAIL_COPY_TO, self::SCENARIO_EMAIL_MOVE_TO])) {
                $file_name = Translit::forFileName($this->file_name);

                if ($file_name === '') {
                    $this->addError($this->file_title, Yii::t('messages', 'Error file name transliteration in Latin script. There are invalid characters'));

                    return false;
                } else {
                    $this->file_name = $file_name;
                }

                $destenation = $path_module . DIRECTORY_SEPARATOR . $this->file_path . DIRECTORY_SEPARATOR . $this->file_name;
                if ($this->scenario == self::SCENARIO_EMAIL_COPY_TO) {
                    if (!copy($this->file_path_copy, $destenation)) {
                        $this->addError($this->file_title, Yii::t('messages', 'Error saving file'));

                        return false;
                    }
                }
                if ($this->scenario == self::SCENARIO_EMAIL_MOVE_TO) {
                    if (!rename($this->file_path_copy, $destenation)) {
                        $this->addError($this->file_title, Yii::t('messages', 'Error saving file'));

                        return false;
                    }
                }
            }

            if ($this->isNewRecord == false) {
                $this->deleteFile($this->file_name_old);
            }
        }

        return true;
    }

    /**
     *   Создание миниатюры
     */
    private function createThumbs($file_path, $file_name)
    {
        if (!$this->thumb_scenario) {
            return;
        }

        $file_type_general = $this->file_type;
        if ($file_type_general == 'attachments' || $file_type_general == 'activity') {
            $file_type_general = $this->getFileTypeClass();
        }

        if ($file_type_general != 'file_image') {
            return;
        }

        $this->thumbs = '1';

        $thumb = new ThumbNail();
        $thumb->Thumblocation = $file_path;

        foreach ($this->file_thumbs as $key => $file_thumb_vars) {
            $thumb->Cropimage = null;
            $thumb->SaveProportional = false;
            $file_thumb_vars['full_file_name'] = $file_path . $file_name;

            if (!in_array($this->thumb_scenario, $file_thumb_vars['scenario'])) {
                continue;
            }

            $file_type = $this->getFileType($file_name);

            if (!empty($file_type) && in_array($file_type, ['bmp', 'tif', 'tga', 'ico'])) {
                copy($file_path . $file_name, $file_path . $file_thumb_vars['title'] . '_' . $file_name);
                continue;
            } elseif (!in_array($file_type, $this->type_class['file_image'])) {
                continue;
            }

            if ($file_thumb_vars['width'] !== '') {
                $thumb->Thumbwidth = $file_thumb_vars['width'];
            }
            if ($file_thumb_vars['height'] !== '') {
                $thumb->Thumbheight = $file_thumb_vars['height'];
            }

            $thumb->Thumbprefix = $file_thumb_vars['title'] . '_';

            // задает пропорциональное уменшение изображения по высоте
            if ($file_thumb_vars['proportional']) {
                $thumb->SaveProportional = true;
            }

            $this->ThumbCropimage($thumb, $file_thumb_vars);

            $thumb->Createthumb($file_path . $file_name, 'file');
        }
    }

    /**
     * getImageSizeCoeficient - возвращает коефициент пропорций изображения
     *
     * @param $file_thumb_vars
     * @return float
     */
    private function getImageSizeCoefficient($width, $height)
    {
        return round($width / $height, 1);
    }

    /**
     * getImageSizeByCoeficient - возвращает новый размер стороны изображения
     * $size - размер одной стороны изобрадения
     * $return_side - сторона: ширина или высота - w|h
     * $crop_coef - коефициент
     */
    private function getImageSizeByCoeficient($size, $coef, $return_side)
    {
        if ($size === 0) {
            return 0;
        }

        if ($return_side == 'w') {
            return round($size * $coef);
        }

        if ($return_side == 'h') {
            return round($size / $coef);
        }
    }

    /**
     * setCropimage - Установка параметров для обрезки картинки
     *
     * @param ThumbNail $thumb
     * @param $c0
     * @param $c1
     * @param $c2
     * @param $c3
     * @param $c4
     * @param $c5
     * @return $this
     */
    private function setCropimage(ThumbNail $thumb, $c0, $c1, $c2, $c3, $c4, $c5)
    {
        $thumb->Cropimage = [$c0, $c1, $c2, $c3, $c4, $c5];

        return $this;
    }

    /**
     * ThumbCropimage - устновка параметров обрезки картинок
     *
     * @param ThumbNail $thumb
     * @param $file_thumb_vars
     */
    private function ThumbCropImage(ThumbNail $thumb, $file_thumb_vars)
    {
        if ($file_thumb_vars['crop'] == false) {
            return;
        }

        $file_info = GetImageSize($file_thumb_vars['full_file_name']);
        $height_real = $file_info[1];
        $width_real = $file_info[0];

        $crop_coef = $this->getImageSizeCoefficient($file_thumb_vars['width'], $file_thumb_vars['height']);
        if ($crop_coef == false) {
            return;
        }

        $height = $height_real;
        $width = $this->getImageSizeByCoeficient($height, $crop_coef, 'w');

        if ($width == $width_real) {
            return;
        }

        if ($width < $width_real) {
            $c = round(($width_real - $width) / 2);
            if ($c <= 0) {
                return;
            }

            return $this->setCropimage($thumb, 1, 1, $c, $c, 0, 0);
        }

        $width = $width_real;
        $height = $this->getImageSizeByCoeficient($width, $crop_coef, 'h');

        $c = round(($height_real - $height) / 2);
        if ($c <= 0) {
            return;
        }

        return $this->setCropimage($thumb, 1, 1, 0, 0, $c, $c);
    }

    /**
     *   Обновление миниатюры
     */
    private function updateThumbImage($location, $file_name, $type = 'square')
    {
        $thumb = new ThumbNail();
        $thumb->Thumblocation = $location;
        //$thumb->Thumbsize = 160;
        switch ($type) {
            case 'square':
                //делаем квадартную картинку
                $thumb->Square = true;
                $thumb->Cropimage = [3, 0, 0, 0, 0, 0];
                break;
        }

        $thumb->Createthumb($location . $file_name, 'file');
    }

    public function getThumbsSizeForDropDown()
    {
        $result = [];
        foreach ($this->file_thumbs as $key => $value) {
            if ($value['constructor_show'] == true) {
                $result[$key] = Yii::t('base', 'Height preview') . ' ' . $value['title'];
            }
        }

        return $result;
    }

    protected function afterDelete()
    {
        $this->deleteFile($this->getFileName());

        return true;
    }

    protected function afterFind()
    {
        if ($this && !$this->fileExist()) {
            $this->unsetAttributes(
                array_diff(
                    array_keys($this->getAttributes()),
                    [
                        'id',
                        'file_name',
                        'file_title',
                        'date_create',
                        'file_date_upload'
                    ])
            );
        }
    }

    /**
     * @param $keys
     * @return null
     */
    public function getLastUploadImgFile($relate_keys)
    {
        if (empty($relate_keys)) {
            return;
        }

        foreach ($relate_keys as $relate_key) {
            $criteria = new CDbCriteria();
            $criteria->condition = 'relate_key=:relate_key AND file_source=:file_source AND status=:status';
            $criteria->params = [
                ':relate_key'  => $relate_key->attachment,
                ':file_source' => 'module',
                ':status'      => 'asserted',
            ];
            $criteria->addInCondition("SUBSTRING_INDEX(file_name,'.',-1)", $this->type_class['file_image']);
            $criteria->order = 'file_date_upload DESC';

            $model = new UploadsModel();
            $model->setDbCriteria($criteria);
            $data = $model->findAll();

            if (!empty($data)) {
                return $data[0]->getFileInfo();
            }
        }
    }

    /**
     * @return bool
     */
    private function fileExist()
    {
        if ($this->getAttribute('file_source') == self::SOURCE_GOOGLE_DOC) {
            return true;
        }

        return self::checkFileExist($this->getFullFileName(true));
    }

    /**
     * @param $path
     * @return bool
     */
    public static function checkFileExist($full_file_name)
    {
        return file_exists($full_file_name);
    }

    /**
     * Удаление файла и директории (если пустая)
     */
    public function deleteFile($file_name)
    {

        $path_module = $this->getFilePath(true);

        if (!file_exists($path_module . '/' . $this->file_path . '/' . $file_name)) {
            return;
        }

        @unlink($path_module . '/' . $this->file_path . '/' . $file_name);

        foreach ($this->file_thumbs as $key => $value) {
            $prefix = $value['title'] . '_';

            if (file_exists($path_module . '/' . $this->file_path . '/' . $prefix . $this->getFileName())) {
                @unlink($path_module . '/' . $this->file_path . '/' . $prefix . $file_name);
            }
        }
        @rmdir($path_module . '/' . $this->file_path);
    }

    /**
     *   Возвращает информацию о файле
     */
    public function getFileInfo($thumb_size = 30)
    {
        return [
            'id'               => $this->id,
            'file_name'        => $this->getFileName(),
            'file_title'       => $this->getFileTitle(),
            'file_date_upload' => date('d', strtotime($this->file_date_upload)) . ' ' .
                mb_strtolower(Yii::t('base', date('F', strtotime($this->file_date_upload)), 2), 'utf-8') . ' ' .
                Yii::t('base', 'in') . ' ' .
                date('H:i', strtotime($this->file_date_upload)),
            'file_url'         => '/' . $this->getFileUrl(),
            'file_thumb_url'   => '/' . $this->getFileThumbsUrl($thumb_size),
            'file_type'        => $this->getFileType(),
            'file_type_class'  => $this->getFileTypeClass(),
            'file_size'        => round($this->getFileSize() / 1024),
            'file_source'      => $this->file_source,
        ];
    }

    /**
     * Возвращает тип группы файла: file_image, file_text, file_table, file_media, file_other
     */
    public function getFileTypeClass($type = null)
    {
        if (empty($type)) {
            $type = $this->getFileType();
        }
        foreach ($this->type_class as $key => $value) {
            if (is_array($value)) {
                if (in_array(mb_strtolower($type), $value)) {
                    return $key;
                }
            } else {
                return $key;
            }
        }
    }

    public function getFileName($full_file_name = null)
    {
        if (!empty($full_file_name)) {
            return basename($full_file_name);
        } elseif ($this->file_name instanceof CUploadedFile) {
            return $this->file_name->getName();
        } else {
            return $this->file_name;
        }
    }

    public function getFileTitle()
    {
        return $this->file_title;
    }

    /**
     * Возвращает название файла для линка
     */
    public function getFileUrl($add_site_url = false)
    {
        if ($this->file_source == self::SOURCE_GOOGLE_DOC) {
            return $this->getFullFileName();
        }

        $url = 'file?id=' . $this->id;

        if ($add_site_url) {
            $url = ParamsModel::getValueFromModel('site_url') . '/' . $url;
        }

        return $url;
    }

    /**
     * Возвращает полный путь к файлу миниатюры
     */
    public function getFileThumbsUrl($size = null)
    {
        return 'file?id=' . $this->id . ($size ? '&size=' . $size : '');
    }

    private function getModulePath($add_root_path = false)
    {
        $path = ParamsModel::model()->titleName('upload_path_module')->find()->getValue();

        if ($add_root_path) {
            $path = YiiBase::app()->basePath . '/../' . $path;
        }

        return $path;
    }

    public function getCommunicationsPath($add_root_path = false)
    {
        $path = ParamsModel::model()->titleName('upload_path_communications')->find()->getValue();

        if ($add_root_path) {
            $path = YiiBase::app()->basePath . '/../' . $path;
        }

        return $path;
    }

    private function getFilePath($add_root_path = false)
    {
        switch ($this->file_source) {
            case self::SOURCE_MODULE:
                $path = $this->getModulePath($add_root_path);
                break;
            case self::SOURCE_COMMUNICATIONS:
                $path = $this->getCommunicationsPath($add_root_path);
                break;
            default:
                $path = $this->getModulePath($add_root_path);
        }

        return $path;
    }

    /**
     * Возвращает название файла з полным путем
     */
    public function getFullFileName($add_root_path = false)
    {
        if ($this->isRealFile()) {
            $path = $this->getFilePath($add_root_path);

            return $path . '/' . $this->file_path . '/' . $this->getFileName();

        } elseif ($this->file_source == self::SOURCE_GOOGLE_DOC) {
            return $this->file_path;
        }
    }

    /**
     * Возвращает полный путь к файлу миниатюры
     */
    public function getFullThumbsFileName($size = null, $add_root_path = false)
    {
        $file_type = $this->file_type;
        if ($file_type == 'attachments' || $file_type == 'activity') {
            $file_type = $this->getFileTypeClass();
        }
        if ($file_type != 'file_image') {
            return;
        }

        $path = ParamsModel::model()->titleName('upload_path_module')->find()->getValue();

        if ($add_root_path) {
            $path = YiiBase::app()->basePath . '/../' . $path;
        }

        if ($size !== null) {
            return $path . '/' .
                $this->file_path . '/' .
                $this->file_thumbs[$size]['title'] . '_' . $this->getFileName();
        } else {
            return $path . '/' .
                $this->file_path . '/' .
                $this->getFileName();

        }
    }

    /**
     * Возвращает полный путь к файлу миниатюры
     */
    public function getFileThumbsParams($size, $param_name = null)
    {
        if (isset($this->file_thumbs[$size])) {
            $params = $this->file_thumbs[$size];
        }

        if ($param_name !== null && isset($params[$param_name])) {
            return $params[$param_name];
        } else {
            return $params;
        }
    }

    /**
     * Возвращает тип файла
     */
    public function getFileType($file_name = null)
    {
        if ($this->isRealFile()) {
            if ($file_name === null) {
                $file_name = $this->getFileName();
            }

            return strtolower(substr($file_name, strripos($file_name, '.') + 1));
        } else {
            if ($this->file_source == self::SOURCE_GOOGLE_DOC) {
                return 'GDoc';
            } else {
                if (!$this->file_source && $file_name) {
                    return strtolower(substr($file_name, strripos($file_name, '.') + 1));
                }
            }
        }
    }

    private function isRealFile()
    {
        if (in_array($this->file_source, [
            self::SOURCE_MODULE,
            self::SOURCE_COMMUNICATIONS,
            self::SCENARIO_EMAIL_COPY_TO,
            self::SCENARIO_EMAIL_MOVE_TO,
            self::SCENARIO_MODULE_COPY_TO,
            self::SCENARIO_MODULE_MOVE_TO,
        ])) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает размер файла
     */
    public function getFileSize($file_full_name = null)
    {
        if ($this->isRealFile()) {
            if ($file_full_name === null) {
                if (!$this->fileExist()) {
                    return 0;
                }
                $file_full_name = $this->getFullFileName(true);
            }

            return filesize($file_full_name);
        }
    }

    public static function getThumbStub()
    {
        return 'static/images/lock_thumb-mini.jpg';
    }

    public function setAddUpdateImage($type)
    {
        $this->add_update_image = $type;

        return $this;
    }

    public function checkImageFile($file_name)
    {
        $file_type = $this->getFileType($file_name);
        if (empty($file_type)) {
            return false;
        }

        return (in_array($file_type, $this->type_class['file_image']));
    }

    public static function getUploadPathModule()
    {
        if (is_null(self::$_upload_path_module)) {
            self::$_upload_path_module = ParamsModel::model()->titleName('upload_path_module')->find()->getValue();
        }

        return self::$_upload_path_module;
    }

    public static function existsRemoteFile($url)
    {
        $file_headers = @get_headers($url);

        $file_exists = false;
        if (!empty($file_headers) && false !== strpos($file_headers[0], '200 OK')) {
            // Проверка MIME-типа: [3] => Content-Type: image/png
            $file_exists = true;
        }

        return $file_exists;
    }

    /**
     *  Копия файла из предложеного пути
     */
    public function copyFromSource($full_file_name)
    {
        if (empty($full_file_name)) {
            return false;
        }

        if (!@file_exists($full_file_name)) {
            if ((substr($full_file_name, 0, 4) != 'http' || substr($full_file_name, 0, 5) != 'https') && !self::existsRemoteFile($full_file_name)) {
                return false;
            }
        }

        //path_module
        $path_module = self::getUploadPathModule();
        //file_title 
        $this->file_title = $this->getFileName($full_file_name);
        //file_name
        $file_name = Translit::forFileName($this->file_title);
        if ($file_name === '') {
            return false;
        }
        $this->file_name = $file_name;

        //file_path
        $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $file_name;
        $this->file_path = md5($relate_key);

        //path
        $file_path = $path_module . '/' . $this->file_path;
        //file_name_to
        $file_name_to = $file_path . '/' . $file_name;

        if (!file_exists($file_path)) {
            mkdir($file_path, 0755);
            if (!file_exists($file_path)) {
                return false;
            }
        }

        copy($full_file_name, $file_name_to);

        $result = file_exists($file_name_to);
        if ($result) {
            $this->createThumbs($file_path . '/', $file_name);
        }

        return $result;
    }

    /**
     * deletePrepareUploads
     */
    public static function deletePrepareUploads($relate_key)
    {
        $data_model = new DataModel();
        $data_model
            ->setSelect('id, file_path, file_name')
            ->setFrom('{{uploads}}')
            ->andWhere('relate_key=:relate_key', [':relate_key' => $relate_key]);

        $data_uploads = $data_model->findAll();

        if (!empty($data_uploads)) {
            foreach ($data_uploads as $uploads) {
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('uploads_data', \QueryDeleteModel::D_TYPE_DATA, ['table_name' => 'uploads', 'primary_field_name' => 'id'])
                    ->appendValues('uploads_data', \QueryDeleteModel::D_TYPE_DATA, $uploads['id']);

                \QueryDeleteModel::getInstance()->appendValues('uploads_file', \QueryDeleteModel::D_TYPE_UPLOADS, ['file_path' => $uploads['file_path'], 'file_name' => null]);
            }
        }
    }

    /**
     * pngToJpg - конвертирует png в jpg
     *
     * @param $original_file
     * @param null $output_file
     * @return bool|string
     */
    function getPngToJpg($original_file, $output_file = null)
    {
        $result = null;

        $type = getimagesize($original_file);
        if ($type['mime'] == "image/png") {
            $source = imagecreatefrompng($original_file);
            $image = imagecreatetruecolor(imagesx($source), imagesy($source));

            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
            imagecopy($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image));

            if ($output_file) {
                imagejpeg($image, $output_file);
            } else {
                ob_start();
                imagejpeg($image, $output_file);
                $result = ob_get_clean();
                ob_flush();
            }

            imagedestroy($image);
            imagedestroy($source);
        }

        return $result;
    }

    /**
     * getEncodeBase64 - возвращает изображение в base64
     *
     * @param null $bin
     * @param null $file_type
     * @return string
     */
    public function getEncodeBase64($bin = null, $file_type = null)
    {
        if ($file_type === null) {
            $file_type = $this->getFileType();
        }

        if ($bin === null) {
            $full_file_name = $this->getFullFileName(true);

            if ($full_file_name == false) {
                return;
            }

            $bin = fread(fopen($full_file_name, "r"), filesize($full_file_name));
        }

        return 'data:image/' . $file_type . ';base64,' . base64_encode($bin);
    }

    /**
     * getEncodeToBase64 - конвертирует изображение в base64 и возвращает его для создания pdf
     *
     * @param null $bin
     * @param null $file_type
     * @return string
     */
    public function getEncodeToBase64()
    {
        $full_file_name = $this->getFullFileName(true);

        $file_type = $this->getFileType();

        $bin = null;
        if ($file_type == 'png') {
            $bin = $this->getPngToJpg($full_file_name);
            $file_type = 'jpg';
        }

        return $this->getEncodeBase64($bin, $file_type);
    }

    /**
     * getImageFoPdf -возвращает изображение для pdf
     *
     * @param null $bin
     * @param null $file_type
     * @return string
     */
    public function getImageFoPdf()
    {
        $full_file_name = $this->getFullFileName(true);

        $file_type = $this->getFileType();

        if ($file_type == 'png') {
            $file_name_temp = FileOperations::getTempFileName('script_');
            $this->getPngToJpg($full_file_name, $file_name_temp);
            $full_file_name = $file_name_temp;

            self::addTempFileNameList($file_name_temp);
        }

        return $full_file_name;
    }

    /**
     * Проверка доступа к файлу
     *
     * @return bool|UploadsModel
     */
    public function checkAccess()
    {
        if ($this->copy_id == false) {
            return false;
        }
        if ($this->copy_id == '-1') {
            return true;
        }
        if (($this->status == 'temp') || ($this->user_create == WebUser::getUserId())) {
            return true;
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($this->copy_id);
        if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $extension_copy->copy_id, Access::ACCESS_TYPE_MODULE)) {
            return false;
        }

        $result = $this->checkAccessCard($extension_copy);
        if ($result === false) {
            $result = $this->checkAccessBlockActivity($extension_copy);
        }

        return $result;
    }

    /**
     * Проверяет доступ к файлу, загруженного в карточку модуля
     *
     * @param $extension_copy
     * @return $this|bool
     */
    private function checkAccessCard($extension_copy)
    {
        $field_types = [Fields::MFT_FILE, Fields::MFT_FILE_IMAGE, Fields::MFT_ATTACHMENTS];
        $field_list = [];

        foreach ($field_types as $field_type) {
            $params = $extension_copy->getFieldSchemaParamsByType($field_type, null, false);
            if ($params) {
                foreach ($params as $param) {
                    $field_list[] = $param['params']['name'];
                }
            }
        }

        $condition = [];
        foreach ($field_list as $field_name) {
            $condition[] = $field_name . ' in ("' . $this->relate_key . '")';
        }

        $card_id = (new DataModel())
            ->setSelect($extension_copy->getPkFieldName())
            ->setFrom($extension_copy->getTableName())
            ->setWhere(implode(' OR ', $condition))
            ->findScalar();

        $check = ParticipantModel::model()->checkUserSubscription(
            $extension_copy->copy_id,
            $card_id
        );

        if ($check) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Проверяет доступ к файлу, загруженного в блок Активность
     *
     * @param $extension_copy
     * @return $this|bool
     */
    private function checkAccessBlockActivity($extension_copy)
    {
        $params = $extension_copy->getFieldSchemaParamsByType(Fields::MFT_ATTACHMENTS, null, false);

        if ($params == false) {
            return false;
        }

        $am_data = (new DataModel())
            ->setFrom('{{activity_messages}}')
            ->setWhere('copy_id = ' . $extension_copy->copy_id . ' AND attachment = "' . $this->relate_key . '"')
            ->findRow();

        if ($am_data == false) {
            return false;
        }

        if (empty($am_data['data_id'])) {
            return true;
        }

        $card_id = (new DataModel())
            ->setSelect($extension_copy->getPkFieldName())
            ->setFrom($extension_copy->getTableName())
            ->setWhere($extension_copy->getPkFieldName() . ' = ' . $am_data['data_id'])
            ->findScalar();

        $check = ParticipantModel::model()->checkUserSubscription(
            $extension_copy->copy_id,
            $card_id
        );

        if ($check) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * fileLoad - Отдача файла в браузер, потоком
     *
     * @return stream
     */
    public static function fileLoad($params)
    {
        if (empty($params['id'])) {
            return false;
        }

        $upload_model = \UploadsModel::model()->findByPk($params['id']);

        if (!$upload_model) {
            return false;
        }

        if (!$upload_model->checkAccess()) {
            return false;
        }

        $upload_model->initFileType($upload_model->getFullFileName(true));

        if ($upload_model->file_type == 'file_image') {
            $size = (!empty($params['size']) ? $params['size'] : null);
            $vars = [
                'file_title'     => $upload_model->file_title,
                'full_file_name' => $upload_model->getFullThumbsFileName($size, true),
            ];

            return self::fileLoadImage($vars);

        } else {
            $vars = [
                'file_title'     => $upload_model->file_title,
                'full_file_name' => $upload_model->getFullFileName(true),
            ];

            return self::fileLoadBin($vars);

        }

        return true;
    }

    /**
     * fileLoad - Отдача бинарного файла в браузер, потоком
     *
     * @return stream
     */
    private static function fileLoadBin($vars)
    {
        if (!file_exists($vars['full_file_name'])) {
            return false;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $file_size = filesize($vars['full_file_name']);
        $file_size = number_format(($file_size / 1024), 2) . 'kB';
        //$mime_type = mb_strtolower(mime_content_type($vars['full_file_name']));

        header('Content-Description: File Transfer');
        //header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename=' . $vars['file_title']);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);

        readfile($vars['full_file_name']);

        return true;
    }

    /**
     * fileLoad - Отдача файла изображения в браузер, потоком
     *
     * @return stream
     */
    private static function fileLoadImage($vars)
    {
        if (!file_exists($vars['full_file_name'])) {
            return false;
        }

        $mime_type = mb_strtolower(mime_content_type($vars['full_file_name']));

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename=' . $vars['file_title']);
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-cache');
        header('Accept-Ranges: bytes');
        readfile($vars['full_file_name']);

        return true;
    }

}






