<?php
$this->_template_letter_data['subject'] = Yii::t('email','Your Quotidian is ready');


$this->_template_letter_data['body'] = '<div>'.Yii::t('email','Hello {user_name}', array('{user_name}'=>'{user_name}')).',</div><br>
<div>'.Yii::t('email',"My name is Yuri, I am the director of development for the Quotidian platform. I'm very glad that you decided to try Quotidian. I am sure you will like the service as soon as you see how much faster and easier it is to manage them, projects and communication within the working team.").'</div><br>
<div>'.Yii::t('email','We created Quotidian to improve business performance, increase transparency and streamline workflows within the team. I am glad that we can help you with this.').'</div><br>
<div>'.Yii::t('email','We are trying to help each of our users to solve specific business problems that they face. If you tell us, after answering this letter, why you decided to try Quotidian, then we can customize the system to solve your business problems.').'</div><br>
<div>'.Yii::t('email','Yuri').'</div>
<div>'.Yii::t('email','Development Director of Quotidian').'</div>
';
