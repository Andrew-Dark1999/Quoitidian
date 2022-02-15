<?php

/**
 * Class DraftManager
 * @method ActiveRecord find()
 *
 * @author Aleksandr Roik
 */
class DraftManager extends AbstractManager
{
    /**
     * @return string
     */
    public function modelClass()
    {
        return DraftModel::class;
    }

    /**
     * @param $uid
     * @return array
     * @throws ModelNotFoundException
     */
    public function getByUid($uid)
    {
        /* @var DraftModel $model */
        $model = $this->find('`uid`=:uid', [':uid' => $uid]);

        if (!$model) {
            throw new \ModelNotFoundException();
        }

        return [
            'type' => $model->data_type,
            'data' => $model->data,
        ];
    }

    /**
     * @param $attributes
     * @return null|Validate
     */
    public function saveText($attributes)
    {
        $attributes['data_type'] = DraftDataTypeDefinition::TEXT;

        return $this->saveByAttributes($attributes);
    }

    /**
     * @param $attributes
     * @return null|Validate
     */
    public function saveJson($attributes)
    {
        $attributes['data_type'] = DraftDataTypeDefinition::JSON;

        return $this->saveByAttributes($attributes);
    }

    /**
     * @param $attributes
     * @return null|Validate
     */
    public function saveByAttributes($attributes)
    {
        /* @var DraftModel $model */

        if(array_key_exists('uid', $attributes)){
            $model = $this->find('`uid`=:uid', [':uid' => $attributes['uid']]);
        }

        if(!$model){
            $className = $this->modelClass();
            $model = new $className();
        }

        $model->setAttributes($attributes);
        $model->save();

        if ($model->hasErrors()) {
            return $model->getErrorsAsValidate();
        }
    }

    /**
     * @param $uid
     * @return bool|Validate
     */
    public function deleteByUid($uid)
    {
        /* @var DraftModel $model */
        $model = $this->find('`uid`=:uid', [':uid' => $uid]);

        if (!$model) {
            throw new \ModelNotFoundException();
        }
        if (!$model->delete()) {
            throw new ModelNotDeleteException();
        }
    }
}
