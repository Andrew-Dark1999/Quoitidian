<?php
/**
 * Created by PhpStorm.
 * User: kastiel
 * Date: 15.04.2015
 * Time: 11:51
 */

/**
 * Class Notices
 */
class Notices  extends CWidget{

    const TYPE_NOTICE = 'notification';
    const TYPE_NOTICE_HND = 'notification_hnd'; // for history notification
    const TYPE_TASK   = 'tasks-bar';
    const TYPE_MESSAGE ='inbox';

    public $type;
    public $data = array();

    public $id;
    private $class;
    private $text;
    private $ico;
    private $result = array();


    public function initAuto(){
        switch($this->id){
            case HeaderNoticeModel::HN_ID_TASKS:
                $this->initTask();
                break;
            case HeaderNoticeModel::HN_ID_NOTICE:
                $this->initNotice();
                break;
        }

        return $this;
    }

    /**
     * @throws CException
     */
    public function header(){
        $this->render('header');
    }

    /**
     * @throws CException
     */
    public function footer(){
        $this->render('footer');
    }

    /**
     * @return $this
     */
    public function setNotice(){
        $this->class = self::TYPE_NOTICE;
        return $this;
    }

    /**
     * @return $this
     */
    public function setNoticeHND(){
        $this->class = self::TYPE_NOTICE_HND;
        return $this;
    }


    /**
     * @return $this
     */
    public function initNotice(){
        $this->setNotice();
        $this->id   = HeaderNoticeModel::HN_ID_NOTICE;
        $this->ico  = 'fa-bell-o';
        $this->text  = Yii::t('base', 'Notifications');
        return $this;
    }

    /**
     * @return $this
     */
    public function setTask(){
        $this->class = self::TYPE_TASK;
        return $this;
    }

    /**
     * @return $this
     */
    public function initTask(){
        $this->setTask();
        $this->id   = HeaderNoticeModel::HN_ID_TASKS;
        $this->ico  = 'fa-tasks';
        $this->text  = Yii::t('base', 'Tasks in the work: <span class="count_total">{s}</span>', array('{s}' => (!empty($this->data['total']) ? $this->data['total'] : 0)));
        return $this;
    }




    /**
     * @return $this
     */
    /*
    public function setMessage(){
        $this->class = self::TYPE_TASK;
        $this->class_counter = 'bg_important';
        return $this;
    }
    */

    /**
     * @return $this
     */
    /*
    public function initMessage(){
        $this->setMessage();
        $this->id   = 'header_inbox_bar';
        $this->ico  = 'fa-envelope-o';
        $this->text  = Yii::t('base', 'Messages');
        return $this;
    }
    */

    /**
     * @throws CException
     */
    public function build($vars){
        $this->render('main_block',
            array(
                'type' => $this->type,
                'text' => $this->text,
                'id' => $this->id,
                'class' => $this->class,
                'ico' => $this->ico,
                'data' => $this->data,
                'notice_set_read' => $vars['notice_set_read'],
            )
        );

        return $this;
    }



    /**
     * @throws CException
     */
    public function buildInner(){
        switch($this->class){
            case self::TYPE_NOTICE:
                $this->render_parts('notice');
                break;
            case self::TYPE_NOTICE_HND:
                $this->render_parts('notice-hnd');
                break;
            case self::TYPE_TASK:
                $this->render_parts('task');
                break;
            case self::TYPE_MESSAGE:
                $this->render_parts('messages');
                break;
        }

        return $this;
    }


    private function getMessageId($data){
        switch($this->class){
            case self::TYPE_NOTICE:
            case self::TYPE_NOTICE_HND:
                return $data['history_model']->history_id;
                break;
            case self::TYPE_TASK:
                return $data['zadachi_id'];
                break;
            case self::TYPE_MESSAGE:
                break;
        }
    }



    private function getContentReloadData($data){
        if($this->class != self::TYPE_TASK) return;

        if($data['process_id'] && $data['unique_index']){
            $vars = array(
                'check_expediency_switch' => true,
                'action_run' => \ContentReloadModel::CR_ACTION_RUN_LOAD_BPM_PROCESS,
                'action_after' => [\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_HIDE_LEFT_MENU, \ContentReloadModel::CR_ACTION_AFTER_SHOW_PROCESS_BPM_OPERATION],
                'module' => array(
                    'copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
                    'process_id' => $data['process_id'],
                    'unique_index' => $data['unique_index'],
                    'process_mode' => 'run',
                ),
            );
        } else {
            $vars = array(
                'check_expediency_switch' => true,
                'action_after' => [\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_HIDE_LEFT_MENU, \ContentReloadModel::CR_ACTION_AFTER_SHOW_EDIT_VIEW],
                'module' => array(
                    'copy_id' => $data['copy_id'],
                    'data_id' => $data['zadachi_id'],
                    'params' => array(
                        'this_template' => 'auto',
                        'pci' => 'auto',
                        'pdi' => 'auto',
                    ),
                ),
            );
        }

        $content_model = (new \ContentReloadModel(6))->addVars($vars)->prepare();

        $action_key = $content_model->getKey();

        return [
            'action_key' => $action_key,
            'vars' => $content_model->getContentVars(false, true, $action_key),
        ];
    }


    /**
     * @param $name_view
     * @throws CException
     */
    private function render_parts($name_view){
        $vars_1 = $this->data;
        unset($vars_1['data']);

        foreach ($this->data['data'] as $data) {
            $vars = array('data' => $data, 'vars' => $vars_1);

            $content_reload = $this->getContentReloadData($data);
            if($content_reload){
                $vars['action_key'] = $content_reload['action_key'];
            }

            $result = array(
                'history_id' => $this->getMessageId($data),
                'html' => $this->render($name_view, $vars, true),
                'new' => $data['new'],
            );

            //link_actions
            if(!empty($data['link_actions'])){
                $result['link_actions'] = $data['link_actions'];
            }

            //content_reload
            if($content_reload){
                $result['content_reload'] = $content_reload['vars'];
            }

            $this->result[] = $result;
        }
    }



    public function getResult(){
        return $this->result;
    }   


    public function getResultConcat(){
        $html = '';
        $link_actions = array();
        $content_reload = array();

        foreach($this->result as $item){
            $html.= $item['html'];
            if(!empty($item['link_actions'])) $link_actions += $item['link_actions'];
            if(!empty($item['content_reload'])) $content_reload += $item['content_reload'];
        }

        return array(
            'html' => $html,
            'link_actions' => $link_actions,
            'content_reload' => $content_reload,
        );
    }







    public function isDateTimeAllDay($params, $value_data){
        $field_name = $params['name'] . '_ad';
        $value = $value_data[$field_name];

        return (bool)$value;
    }


}
