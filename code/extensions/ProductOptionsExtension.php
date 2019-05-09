<?php
/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 10.38
 */

namespace Silvershop\SimpleOptions\Extensions;


class ProductOptionsExtension extends \DataExtension
{
    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = array(
        'ProductOptions' => 'SimpleProductOption'
    );

    public function updateCMSFields(\FieldList $fields)
    {
        if($this->canUseSimpleOptions()){
            $fields->addFieldToTab('Root.SimpleOptions', \LiteralField::create("vanotice", "<p style=\"color:red;\">You can not apply options when you've applied variations</p>"));
        }
        if(!$this->canUseSimpleOptions()){
            $config = \GridFieldConfig_RecordEditor::create(10);
            $config->addComponent(new \GridFieldOrderableRows("Sort"));
            $gridfield = \GridField::create('ProductOptions', 'Simple Options', $this->owner->ProductOptions(), $config);
            $fields->addFieldToTab('Root.SimpleOptions', $gridfield);

            $fields->addFieldToTab('Root.Variations', \LiteralField::create("vanotice", "<p style=\"color:red;\">You can not apply variations when you've applied options</p>"));
            $fields->removeFieldFromTab('Root.Variations', "Variations");
            $fields->removeFieldFromTab('Root.Variations', "VariationAttributeTypes");
        }
    }

    /**
     * @return bool
     */
    public function canUseSimpleOptions()
    {
        return $this->owner->hasExtension('ProductVariationsExtension') && $this->owner->Variations()->exists();
    }

    public function contentcontrollerInit($controller)
    {
        if ($this->owner->ProductOptions()->exists()) {
            $controller->formclass = 'SimpleOptionsForm';
        }
    }

    public function onAfterDuplicate($original, $doWrite)
    {
        $options = $original->ProductOptions();
        if(!$options->exists()){
            return;
        }

        foreach ($options as $option){
            $clonedOption = $option->duplicate();
            $this->owner->ProductOptions()->add($clonedOption);
        }
    }
}