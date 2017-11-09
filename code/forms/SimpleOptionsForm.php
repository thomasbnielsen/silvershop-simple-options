<?php

/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 15.05
 */
class SimpleOptionsForm extends AddProductForm
{
    protected $requiredFields = [];

    public function __construct($controller, $name = "SimpleOptionsForm")
    {
        parent::__construct($controller, $name);

        $this->extend('updateSimpleOptionsForm');
    }

    protected function getFormFields($controller = null)
    {
        $fields = parent::getFormFields($controller);

        if (!$controller) {
            return $fields;
        }

        $p = $this->getBuyable();

        if (!$p || !$p->ProductOptions()->exists()) {
            return $fields;
        }

        // add simple options field
        foreach ($p->ProductOptions() as $option) {
            $fields->push($option->getFormField());
            $this->requiredFields[] = "ProductOptions_".$option->ID;
        }

        return $fields;
    }

    public function getBuyable($data = null)
    {
        if(!$this->controller){
            $this->controller = Controller::curr(); // this somehow bugged out where no controller was found
        }
        if ($this->controller->dataRecord instanceof Buyable) {
            return $this->controller->dataRecord;
        }
        return DataObject::get_by_id('Product', (int)$this->request->postVar("BuyableID")); //TODO: get buyable
    }

    /**
     * @return Validator Validator for this form.
     */
    protected function getFormValidator()
    {
        $validator = parent::getFormValidator();

        $f = RequiredFields::create($this->requiredFields);

        return $validator->appendRequiredFields($f);
    }
}