<?php

class ConsoleQueueModel {


    const STATUS_RUNNING    = 'running';
    const STATUS_DONE       = 'done';

    public static $table_name = '{{console_queue}}';

    private static $_minutes_limit = 30;



    public static function checkRunning($command_name){
        $data = DataModel::getInstance()
                                    ->setFrom(self::$table_name)
                                    ->setWhere(
                                        'status=:status AND command_name=:command_name',
                                        array(
                                            ':status' => self::STATUS_RUNNING,
                                            ':command_name' => $command_name,
                                        ))->findAll();

        if(empty($data)) return false;

        $result = false;
        $id_list = array();

        foreach($data as $row){
            $date1 = new \DateTime($row['date_start']);
            $date2 = new \DateTime();
            $date_interval = $date1->diff($date2);

            $minute = 0;
            if($date_interval->y){
                $minute+= $date_interval->y * 60 * 24 * 365;
            }
            if($date_interval->m){
                $minute+= $date_interval->m * 60 * 24 * 31;
            }
            if($date_interval->d){
                $minute+= $date_interval->d * 60 * 24;
            }
            if($date_interval->h){
                $minute+= $date_interval->h * 60;
            }
            if($date_interval->i){
                $minute+= $date_interval->i;
            }

            if($minute > self::$_minutes_limit){
                $id_list[] = $row['id'];
            } else {
                $result = true;
            }
        }

        if(!empty($id_list)){
            self::saveDone($command_name, $id_list);
        }

        return $result;
    }




    public static function saveRunning($command_name){
        \DataModel::getInstance()->insert(
                                        self::$table_name,
                                        array(
                                            'command_name' => $command_name,
                                            'status' => self::STATUS_RUNNING,
                                            'date_start' => date('Y-m-d H:i:s'),
                                        ));
    }



    public static function saveDone($command_name, $id = null){
        if(!empty($id)){
            if(is_array($id)){
                $id = implode(',', $id);
            }
        }

        $data_model = new \DataModel();
        $data_model->setText('UPDATE ' . self::$table_name  . ' SET status = "' . self::STATUS_DONE . '" WHERE status = "'.self::STATUS_RUNNING.'" AND command_name="'.$command_name.'"'.(!empty($id) ? ' AND id in('.$id.')' : ''))->execute();
    }




    public static function delete($command_name, $status){
        DataModel::getInstance()->delete(
                                self::$table_name,
                                'status=:status AND command_name=:command_name',
                                array(
                                    ':status' => $status,
                                    ':command_name' => $command_name,
                                ));
    }

    public static function deleteOldRecords($limitDate)
    {
        $limitDate = '"' . $limitDate . '"';
        DataModel::getInstance()->delete(self::$table_name, 'date_start < ' . $limitDate);

    }


}
