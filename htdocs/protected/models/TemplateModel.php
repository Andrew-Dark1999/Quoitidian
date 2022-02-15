<?php

class TemplateModel
{

    private $_template_item_list;

    public function setTemplateItem(TemplateItemModel $template_item_model)
    {
        $this->_template_item_list[] = $template_item_model;
    }

    public function prepateDefaultTemplates()
    {
        $this->setTemplateItem((new TemplateItemModel(\TemplateItemModel::CATEGORY_VIEWS, 'site/calendarViewTemplate')));

        return $this;
    }

    /**
     * Возвращает список шаблонов для класса crmParams на фронте
     * [
     * 'site/calendarView' : [
     * 'html' : 'html шаблон',
     * ],
     * ]
     */
    public function getJsTemplateList()
    {
        $template_list = [];

        foreach ($this->_template_item_list as $template_item_model) {
            $template_list[$template_item_model->getViewAlias()] = $template_item_model->getJsTemplate();
        }

        return $template_list;
    }

}

