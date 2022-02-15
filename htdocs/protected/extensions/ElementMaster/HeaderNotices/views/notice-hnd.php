<tr>
    <th style="padding-right: 12px; vertical-align: top;  padding-bottom: 35px;">
        <?php
            ExtensionModel::model()->findByPk(ExtensionModel::MODULE_USERS)->getModule();
            $user_model = UsersModel::model()->findByPk($data['history_model']->user_create);
            $avatar_src = '';
            if(empty($user_model->ehc_image1)) {
                $avatar_src = UploadsModel::getThumbStub();
            } else {
                $file = UploadsModel::model()
                        ->setRelateKey($user_model->ehc_image1)
                        ->find();
                if($file){
                    $avatar_src = $file
                        ->setFileType('file_image')
                        ->getFileThumbsUrl(32);
                }
            }

        ?>
        <img src="<?php echo $vars['site_url'] . '/' . $avatar_src ?>" style="width:auto;height:auto;max-width:34px;max-height:50px" />
    </th>
    <td style="vertical-align: top;  padding-bottom: 15px;">
        <?php echo $data['message_data']['subject']; ?>
        <p style="padding: 0; margin: 0; color:#646464;"><?php echo $data['message_data']['message']; ?></p>
        <i><?php echo DateTimeOperations::getDateTimeOldStr($data['history_model']->date_create); ?></i>
    </td>
</tr>
