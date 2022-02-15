<?php
$this->_template_letter_data['subject'] = Yii::t('email','Is Quotidian right for you?');


$this->_template_letter_data['body'] = '<div>'.Yii::t('email','Hello {user_name}', array('{user_name}'=>'{user_name}')).',</div><br>
<div>'.Yii::t('email',"You registered in our system a week ago and I would like to know what you liked, what did not, if there were any difficulties when you studied Quotidian. If it's not difficult, just answer me on this letter - share your thoughts.").'</div><br>
<div>'.Yii::t('email',"In any case, thank you for your interest. We welcome every new user and value the opinions of people who have spent their time getting to know Quotidian.").'</div><br>
<div>'.Yii::t('email','Yuri').'</div>
<div>'.Yii::t('email','Development Director of Quotidian').'</div>
';
