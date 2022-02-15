<?php
/**
 * FilterConcatModel
 *
 * @author Alex R.
 */

class FilterConcatModel
{

    private $_query;

    private $_result_query;

    private $_box = [];

    public static function getInstance()
    {
        return new self();
    }

    public function setQuery($query)
    {
        $this->_query = $query;

        return $this;
    }

    public function getResultQuery()
    {
        return $this->_result_query;
    }

    /**
     * concat
     */
    public function concat()
    {
        $query = \Helper::arraySort($this->_query, 'full_field_name');
        $fn = null;

        foreach ($query as $item) {
            if ($fn === null || $item['full_field_name'] == $fn) {
                $this->addToBox($item['query'], false);
            } elseif ($fn !== null && $item['full_field_name'] != $fn) {
                $this->addToBox($item['query'], true);
            }
            $fn = $item['full_field_name'];
        }
        $this->reset();

        return $this;
    }

    /**
     * reset
     */
    private function reset()
    {
        $count = count($this->_box);
        if ($count === 1) {
            $this->_result_query[] = $this->_box[0];
        } elseif ($count > 1) {
            array_unshift($this->_box, 'OR');
            $this->_result_query[] = $this->_box;
        }
        $this->_box = [];
    }

    /**
     * addToBox
     *
     * @param $query
     * @param $reset
     */
    private function addToBox($query, $reset)
    {
        if ($reset) {
            $this->reset();
        }
        $this->_box[] = $query;
    }

}
