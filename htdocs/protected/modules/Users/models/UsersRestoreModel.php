<?php
/**
 * UsersRestoreModel
 */
class UsersRestoreModel extends ActiveRecord
{
    public $tableName = 'users_restore';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'UsersModel', 'users_id'),
        );
    }


    public function getRestoreUser($data)
    {
        return self::model()
            ->with(
                array('user' =>  array(
                    'condition'=>'email=:email',
                    'params'=>array(':email'=>$data['email']),
                ))
            )->find(
                array(
                    'condition'=>'created_at>=:time',
                    'params'=>array(':time'=>date('Y-m-d H:i:s', strtotime('-1 hour')))
                ),
                array(
                    'condition'=>'token=:token',
                    'params'=>array(':token'=>$data['token'])
                )
            );
    }



}