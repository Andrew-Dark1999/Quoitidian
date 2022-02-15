<?php

/**
 * Загрузка файла в блок Актичность
 * ActionModuleActivityUploadFile
 *
 * @property ActionModuleUploadFileValidator $validator
 * @author Alex R.
 */
class ActionModuleActivityUploadFile extends AbstractActionModuleUploadFile
{
    /**
     * @return mixed
     */
    public function getData()
    {
        $data = parent::getData();

        $data['thumb_scenario'] = FileUpload::THUMB_SCENARIO_ACTIVITY; // для блока Активность
        $data['file_type'] = 'activity'; // для блока Активность
        $data['copy_id'] = -1;

        return $data;
    }
}
