<?php

/**
 * This class will be used to store chosen options on the order. Will be related to an OrderItem, which is then connected to the Order
 *
 *
 * Class OrderOptionItem
 */
class OrderOptionDataItem extends DataObject
{
    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = array(
        'Title'       => 'Varchar',
        'OptionTitle' => 'Varchar',
        'ValueTitle'  => 'Varchar',
        'UnitPrice'   => 'Currency(19,4)',
    );

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     *
     * @var array
     */
    private static $has_one = array(
        'OrderItem'                => 'OrderItem',

        // NOTE:
        //These has one relations should not be used after order is processed.
        //They're used for "unique checking" while order is in Cart
        'SimpleProductOption'      => 'SimpleProductOption',
        'SimpleProductOptionValue' => 'SimpleProductOptionValue',
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Title) {
            $this->Title = $this->generateProperTitle();
        }
    }

    /**
     * @return string
     */
    public function generateProperTitle()
    {
        $title = $this->OptionTitle . ': ' . $this->ValueTitle;

        if ($this->UnitPrice != '0.0000') {
            $title .= ' (+' . $this->dbObject('UnitPrice')->Nice() . ')';
        }

        $this->extend('updateProperTitle', $title);

        return $title;
    }
}