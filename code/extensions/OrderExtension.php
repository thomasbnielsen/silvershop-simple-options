<?php
/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 07/11/2017
 * Time: 14.30
 */

namespace Silvershop\SimpleOptions\Extensions;


class OrderExtension extends \DataExtension
{
    public function afterAdd($item, $buyable, $quantity, $filter)
    {
        $items = $this->createOrderOptionDataItems($filter, $item);
    }

    protected function createOrderOptionDataItems($data, \OrderItem $item)
    {
        $items = \ArrayList::create();

        // figure out which options were submitted and values chosen
        $optionDataList = self::filterDataForSimpleOptions($data);
        if (empty($optionDataList)) {
            return $items;
        }

        // remove any existing options
        if($item->OrderOptionDataItems()){
            $item->OrderOptionDataItems()->removeAll();
        }
        foreach ($optionDataList as $arr) {
            $option = \DataObject::get_by_id("SimpleProductOption", $arr['OptionID']);
            $value = $option->Values()->filter(['ID' => $arr['ValueID']])->first();

            $i = \OrderOptionDataItem::create([
                'SimpleProductOptionID'      => $option->ID,
                'SimpleProductOptionValueID' => $value->ID,
                'OptionTitle'                => $option->Title,
                'ValueTitle'                 => $value->Title,
                'UnitPrice'                  => $value->Price
            ]);
            $i->write();

            $item->OrderOptionDataItems()->add($i);
        }

        // trigger recalc
        $item->forceChange();
        $item->write();

        return $items;
    }

    /**
     * Check associative array for any keys like "ProductOptions_{$ID}".
     * This $ID would be the ID of a SimpleProductOption and this key points to the ID of SimpleProductOptionValue
     *
     * method returns an array like
     *  [
     *  "OptionID" => {$ID},
     *  "ValueID" => {$ID}
     * ]
     *
     * @param $data
     * @return array
     */
    public static function filterDataForSimpleOptions($data)
    {
        $optionDataList = [];
        foreach ($data as $field => $val) {
            $parts = explode('_', $field);
            if ($parts[0] != 'ProductOptions') {
                continue;
            }
            if (isset($parts[1])) {
                $optionDataList[] = ["OptionID" => $parts[1], "ValueID" => $val];
            }
        }

        return $optionDataList;
    }
}