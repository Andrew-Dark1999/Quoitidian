<?php

// header
Yii::app()->controller->widget('ext.ElementMaster.HeaderNotices.Notices')->header();


// tasks
Yii::app()->controller->widget('ext.ElementMaster.HeaderNotices.Notices', array('data' => \TaskModel::getUserTasks()))
                ->initTask()
                ->build(array('notice_set_read' => History::getStatusSetReader(HistoryMessagesModel::MODULE_TYPE_TASK)));

// notice
Yii::app()->controller->widget('ext.ElementMaster.HeaderNotices.Notices', array('data' => History::getInstance()->getFromHistory(HistoryMessagesModel::OBJECT_NOTICE)))
                ->initNotice()
                ->build(array('notice_set_read' => History::getStatusSetReader(HistoryMessagesModel::MODULE_TYPE_BASE)));


// footer
Yii::app()->controller->widget('ext.ElementMaster.HeaderNotices.Notices')->footer();

