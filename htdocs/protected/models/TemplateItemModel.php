<?php

class TemplateItemModel
{

    const CATEGORY_VIEWS = 'views';
    const CATEGORY_COMPONENT = 'extension';

    private $_category;

    private $_view_alias;

    private $_data;

    public function __construct($category, $view_alias, $data = null)
    {
        $this->_category = $category;
        $this->_view_alias = $view_alias;
        $this->_data = $data;

        return $this;
    }

    public function getCategory()
    {
        return $this->_category;
    }

    public function getViewAlias()
    {
        return $this->_view_alias;
    }

    public function getJsTemplate()
    {
        return [
            'html' => $this->getHtml(),
        ];
    }

    private function getHtml()
    {
        switch ($this->_category) {
            case self::CATEGORY_VIEWS:
                return $this->getHtmlCategoryView();
            case self::CATEGORY_COMPONENT:
                return $this->getHtmlCategoryCompenent();
        }
    }

    private function getHtmlCategoryView()
    {
        return \Yii::app()->controller->renderPartial(ViewList::getView($this->_view_alias), $this->_data, true);
    }

    private function getHtmlCategoryCompenent()
    {
        return \Yii::app()->controller->widget(ViewList::getView($this->_view_alias), $this->_data, true);
    }

}
