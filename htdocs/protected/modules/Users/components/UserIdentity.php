<?php 


class UserIdentity extends CUserIdentity{
    
    private $_id;

    public function setUserState($user_model){
        $this->setState('email', $user_model->email);
        $this->setState('sur_name', $user_model->sur_name);
        $this->setState('first_name', $user_model->first_name);
        $this->setState('father_name', $user_model->father_name);
    }
    
    public function authenticate(){
        $user_model = UsersModel::model()->activeUsers()->findByAttributes(array('email'=>$this->username));

        if($this->password === null || $this->password === ''){
            $this->password = 'qwertyuiop123';
        }

        if($user_model===null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else if(!CPasswordHelper::verifyPassword($this->password, $user_model->password))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else{
            $this->_id = $user_model->getPrimaryKey();
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
 
    
    
    public function getId(){
        return $this->_id;
    }



    public function setId($id){
        $this->_id = $id;
    }




}
