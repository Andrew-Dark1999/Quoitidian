<?php

/**
 * Загрузка файла для сущности модуля
 * AbstractActionModuleUploadFile
 *
 * @property ActionModuleUploadFileValidator $validator
 * @author Alex R.
 */
abstract class AbstractActionModuleUploadFile extends AbstractAction
{
    /**
     * @var mixed
     */
    protected $result;

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $data = parent::getData();

        $data['thumb_scenario'] = FileUpload::THUMB_SCENARIO_UPLOAD;
        $data['file_type'] = 'file';

        return $data;
    }

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionModuleUploadFileValidator::class;
    }

    /**
     * Сохраняем данные
     *
     * @return bool
     */
    public function upload()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $fileUpload = new FileUpload($this->getData());

        if ($fileUpload->upload()) {
            $this->result = $fileUpload->getUploadsModel()->getPrimaryKey();

            return true;
        } else {
            $this->validator->addValidateResult('e',  $fileUpload->getUploadsModel()->getErrors());
        }

        return false;
    }

}
