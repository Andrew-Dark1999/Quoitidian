<?php

/**
 * Загрузка файла для сущности модуля
 * ActionModuleUploadFileImage
 *
 * @property ActionModuleUploadFileValidator $validator
 * @author Alex R.
 */
class ActionModuleUploadFileImage extends AbstractActionModuleUploadFile
{
    /**
     * @return mixed
     */
    public  function getData()
    {
        $data = parent::getData();

        $data['thumb_scenario'] = FileUpload::THUMB_SCENARIO_UPLOAD;
        $data['file_type'] = 'file_image';

        return $data;
    }
}
