<?php

/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 10.27
 */
class SimpleProductOption extends DataObject
{
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = "Simple Option";

    /**
     * Human-readable plural name
     *
     * @var string
     * @config
     */
    private static $plural_name = "Simple Options";

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar',
        'Sort'  => 'Int'
    ];

    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = [
        'Values' => 'SimpleProductOptionValue'
    ];

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = [
        'Product' => 'Product'
    ];

    private static $summary_fields = [
        'Title'            => 'Title',
        'getOptionsString' => 'Options'
    ];

    /**
     * Returns a FieldList with which to create the main editing form. {@link DataObject::getCMSFields()}
     *
     * @return FieldList The fields to be displayed in the CMS.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(["Sort", "ProductID", "Values"]);

        $str = '';
        $options = self::get();
        if ($options->exists()) {
            $str = 'Existing options: ' . implode(", ", $options->column("Title"));
        }

        $fields->addFieldToTab('Root.Main', TextField::create("Title")->setDescription($str));

        $comp = new GridFieldEditableColumns();
        $comp->setDisplayFields([
            'Title' => [
                'title' => 'Title',
                'field' => 'TextField'
            ],
            'Price' => [
                'title' => 'Price',
                'field' => 'NumericField'
            ]
        ]);

        $btn = new GridFieldAddNewInlineButton();
        $btn->setTitle(_t('GridField.Add', 'Add {name}', array('name' => self::config()->get("singular_name"))));

        $config = \GridFieldConfig_RecordEditor::create(30);
        $config
            ->removeComponentsByType("GridFieldDataColumns")
            ->removeComponentsByType("GridFieldAddNewButton")
            ->removeComponentsByType('GridFieldEditButton')
            ->removeComponentsByType('GridFieldDeleteAction')
            ->addComponent($comp)
            ->addComponent($btn)
            ->addComponent(new GridFieldDeleteAction())
            ->addComponent(new \GridFieldOrderableRows("Sort"));
        $gridfield = \GridField::create('Values', 'Values', $this->Values(), $config);
        $fields->addFieldToTab('Root.Main', $gridfield);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * Only for use in summary fields
     *
     * @return string
     */
    protected function getOptionsString()
    {
        $arr = [];
        if ($this->Values()->exists()) {
            foreach ($this->Values() as $val) {
                $arr[] = $val->LongTitle();
            }
        }

        return implode(', ', $arr);
    }

    /**
     * @return SS_List
     */
    public function getSortedValues()
    {
        return $this->Values()->sort("Sort");
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = (string)$this->Title;

        $this->extend('updateLabel', $label);

        return $label;
    }

    /**
     * @return DropdownField
     */
    public function getFormField()
    {
        $values = $this->Values()->map("ID", "LongTitle");

        $title = self::getFormFieldName($this->ID);

        $field = DropdownField::create($title, $this->getLabel(), $values);
        $field
            ->setAttribute("data-prices", json_encode($this->Values()->map("ID", "Price")->toArray()))// enable JS to easily get price value
            ->setEmptyString(_t('SimpleProductOption.Choose', 'Choose {name}', array('name' => $this->getLabel())));

        $this->extend('updateFormField', $field);

        return $field;
    }

    /**
     * @param $id
     * @return string
     */
    public static function getFormFieldName($id)
    {
        return 'ProductOptions_' . $id;
    }

    public function duplicate($doWrite = true)
    {
        $clone = parent::duplicate($doWrite);

        $values = $this->Values();
        if ($values->exists()) {
            foreach ($values as $value) {
                $clonedVal = $value->duplicate();
                $clone->Values()->add($clonedVal);
            }
        }

        $clone->invokeWithExtensions('onAfterDuplicate', $this, $doWrite);

        return $clone;
    }
}