<?php


class HelperEmail{


    private $_html_text;


    public function setHtmlText($html_text){
        $this->_html_text = $html_text;
        return $this;
    }


    public function getHtmlText(){
        return $this->_html_text;
    }


    private function pregReplace($pattern, $replacement = ''){
        $this->_html_text = preg_replace($pattern, $replacement, $this->_html_text);
    }


    /**
     * Удалить скобки <> для email адресов
     */
    public function htmlToTextEmails(){
        $this->_html_text = preg_replace("/<([\w]{1,50}[@][\w.]{1,50})>/", "$1", $this->_html_text);
        return $this;
    }


    /**
     * Переводин письмо из html в текстовый формат
     */
    public function htmlToText(){
        \Yii::import('ext.Html2Text', true);
        $this->_html_text = \Html2Text\Html2Text::convert($this->_html_text);

        return $this;
    }



    /**
     * Очистить сообщение он технической информании, что добавляется сервисом при Пересылке или ответе не письмо
     */
    public function clearResponseText(){
        $this->pregReplace('/(<br>){1,2}[>]+.*<br>--<br>/i', '<br><br>--<br>');
        $this->pregReplace('/(<br>){1,2}[>]+/i', '<br>');
        $this->pregReplace('/(<br> ){1,2}/i', '<br>');

        // Пересланное письмо
        $this->pregReplace('/(<br>){1}[-]{7,}[.]{0,1}[(]{0,1}.*[)]{0,1}.{0,1}[-]{7,}.*/ui'); //пересланные письма с yandex и google
        $this->pregReplace('/[-]{7,}[.]{0,1}[(]{0,1}.*[)]{0,1}.{0,1}[-]{7,}.*/ui'); //пересланные письма с yandex и google


        // Отвеченное письмо: дата, информация об отправилелях
        $this->pregReplace('/(<br>){1}[\d]{4}[-][\d]{1,2}[-][\d]{1,2}[,]{0,1} [\d]{1,2}[:][\d]{2}[,]{0,1} [a-z]*[+][\d]{2}[:][\d]{2}[,]{0,1} [^:]*:.*/ui'); //2018-04-01 12:34 *** : ***
        $this->pregReplace('/[\d]{4}[-][\d]{1,2}[-][\d]{1,2}[,]{0,1} [\d]{1,2}[:][\d]{2}[,]{0,1} [a-z]*[+][\d]{2}[:][\d]{2}[,]{0,1} [^:]*:.*/ui'); //2018-04-01 12:34 *** : ***

        $this->pregReplace('/(<br>){1}[\d]{1,2}[.][\d]{1,2}[.][\d]{4}[,]{0,1} [\d]{1,2}[:][\d]{2}[,]{0,1}[^:]*:.*/ui'); //13.01.2018, 12:34, *** : ***
        $this->pregReplace('/[\d]{1,2}[.][\d]{1,2}[.][\d]{4}[,]{0,1} [\d]{1,2}[:][\d]{2}[,]{0,1}[^:]*:.*/ui'); //13.01.2018, 12:34, *** : ***



        $this->pregReplace('/(<br>){1}[a-zA-Zа-яА-Я]{3,15}[,]{1} [\d]{1,2} [a-zA-Zа-яА-Я]{3,15} [\d]{4}.{1,5}[,]{1} [\d]{1,2}[:][\d]{2}[,]{0,1} [^:]*:.*/ui'); //<br>четверг, 13 апреля 2018 г., 12:34 **** *** и до конца
        $this->pregReplace('/(<br>){1}[\d]{1,2} [a-zA-Zа-яА-Я]{3,15} [\d]{4}.{1,5}[,]{1} [\d]{1,2}[:][\d]{2}[,]{0,1} [^:]*:.*/ui'); //<br>13 апреля 2018 г., 12:34 **** *** и до конца
        $this->pregReplace('/[\d]{1,2} [a-zA-Zа-яА-Я]{3,15} [\d]{4}.{1,5}[,]{1} [\d]{1,2}[:][\d]{2}[,]{0,1} [^:]*:.*/ui'); //13 апреля 2018 г., 12:34 **** *** и до конца

        $this->pregReplace('/(<br>){1}[a-zA-Zа-яА-Я]{3,15}[,]{1} [a-zA-Zа-яА-Я]{3,15} [\d]{1,2}[,] [\d]{4} [\d]{1,2}[:][\d]{2} [^:]*:.*/ui'); //<br>Thursday, April 19, 2018 11:21 AM +03:00 from A.Roik <a.roik@quotidian.cl>:<br><br>4001<br><br>
        $this->pregReplace('/[a-zA-Zа-яА-я]{3,15}[,]{1} [a-zA-Zа-яА-я]{3,15} [\d]{1,2}[,] [\d]{4} [\d]{1,2}[:][\d]{2} [^:]*:.*/ui'); //Thursday, April 19, 2018 11:21 AM +03:00 from A.Roik <a.roik@quotidian.cl>:<br><br>4001<br><br>


        $this->pregReplace('/(<br>){1}[-]{15,100}(<br>){0,}(От|From|Від):.*/ui'); // отвеченные или пересланные письма с outlook
        $this->pregReplace('/[-]{15,100}(<br>){0,}(От|From|Від):.*/ui'); // отвеченные или пересланные письма с outlook


        $this->pregReplace('/(<br>){1}(От|From|Від):.*(Надіслано|Отправлено|Date|Sent):.*(Кому|To):.*(Тема|Subject):.*/ui'); //отвеченные письма с outlook
        $this->pregReplace('/(От|From|Від):.*(Надіслано|Отправлено|Date|Sent):.*(Кому|To):.*(Тема|Subject):.*/ui'); //отвеченные письма с outlook

        $this->pregReplace('/(<br>){1}(От|From|Від):.*(Дата|Date|Sent):.*(Кому|To):.*/ui'); //отвеченные письма
        $this->pregReplace('/(От|From|Від):.*(Дата|Date|Sent):.*(Кому|To):.*/ui'); //отвеченные письма

        $this->pregReplace('/(<br>){1}(On) [a-zA-Zа-яА-Я]{2,15}[,]{1} [a-zA-Zа-яА-Я]{2,15} [\d]{1,2}[,] [\d]{4} (at) [\d]{1,2}[:][\d]{1,2} [^:]*:.*/ui'); //On Wed, Apr 18, 2018 at 4:09 PM, Alex R <roichik@mail.com> wrote:<br>2001<br><br>
        $this->pregReplace('/(On) [a-zA-Zа-яА-я]{2,15}[,]{1} [a-zA-Zа-яА-я]{2,15} [\d]{1,2}[,] [\d]{4} (at) [\d]{1,2}[:][\d]{1,2} [^:]*:.*/ui'); //On Wed, Apr 18, 2018 at 4:09 PM, Alex R <roichik@mail.com> wrote:<br>2001<br><br>

        $this->pregReplace('/^(<br>)*/ui');
        $this->pregReplace('/(<br>)*$/ui');

        return $this;
    }




}


?>
