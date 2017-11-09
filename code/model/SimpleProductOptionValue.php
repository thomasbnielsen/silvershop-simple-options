<?php

/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 10.27
 */
class SimpleProductOptionValue extends DataObject
{
    /**
     * Human-readable singular name.
     * @var string
     * @config
     */
    private static $singular_name = "Option Value";

    /**
     * Human-readable plural name
     * @var string
     * @config
     */
    private static $plural_name = "Option Values";

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar',
        'Sort'  => 'Int',
        'Price' => 'Currency(19,4)'
    );

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = array(
        'ProductOption' => 'SimpleProductOption'
    );

    /**
     * Returns a FieldList with which to create the main editing form. {@link DataObject::getCMSFields()}
     *
     * @return FieldList The fields to be displayed in the CMS.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['Sort']);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * @return string
     */
    public function LongTitle()
    {
        $title = (string)$this->Title;

        if($this->Price !== '0.0000'){
            $title .= ' (+' . $this->dbObject("Price")->Nice() . ')';
        }

        $this->extend('updateLongTitle', $title);

        return $title;
    }
}