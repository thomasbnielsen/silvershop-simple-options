<?php
/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 13.22
 */

namespace Silvershop\SimpleOptions\Extensions;


class OrderItemExtension extends \DataExtension
{
    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = array(
        'OrderOptionDataItems' => 'OrderOptionDataItem'
    );

    public function updateUnitPrice(&$unitprice)
    {
        if(!$this->owner->OrderOptionDataItems()->exists()){
            return;
        }

        foreach($this->owner->OrderOptionDataItems() as $item){
            $unitprice += $item->UnitPrice;
        }
    }
}