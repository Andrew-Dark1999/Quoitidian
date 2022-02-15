<?php
$this->_template_letter_data['subject'] = Yii::t('email',"Quotidian - we want to help with the setup, but we cannot contact you");


$this->_template_letter_data['body'] = '<div>'.Yii::t('email','Hello {user_name}', array('{user_name}'=>'{user_name}')).',</div><br>
<div>'.Yii::t('email',"Since you have registered in the test version of the system, a free Skype consultation for 15-30 minutes is available to you to configure the system for the tasks of your company. I tried to call the number you specified to agree on the day and time, but it is not available.").'</div><br>
<div>'.Yii::t('email', "Often, upon first acquaintance, it is not easy for users to see all the possibilities of our platform, but they are colossal, believe me! Our consultation is the best way to do this. We are always happy to help those who are looking for effective tools to grow their business.").'</div><br>
<div>'.Yii::t('email',"I really hope for any feedback from you.").'</div>
<div>'.Yii::t('email',"Thank you in advance.").'</div><br>
<div>'.Yii::t('email','Yuri').'</div>
<div>'.Yii::t('email','Development Director of Quotidian').'</div>
';
