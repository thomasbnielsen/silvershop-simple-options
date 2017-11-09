<?php

/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 15.53
 */
class SimpleOptionOrderItem extends Product_OrderItem
{
    public function uniquedata()
    {
        $uniquedata = parent::uniquedata();

        if (!$this->OrderOptionDataItems()->exists()) {
            return $uniquedata;
        }

        // do this to have various shopping cart links still working
        foreach ($this->OrderOptionDataItems() as $item){
            $key = SimpleProductOption::getFormFieldName($item->SimpleProductOptionID);
            $uniquedata[$key] = $item->SimpleProductOptionValueID;
        }

        return $uniquedata;
    }
}