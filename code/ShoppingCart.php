<?php

namespace Silvershop\SimpleOptions;

use Silvershop\SimpleOptions\Extensions\OrderExtension;

class ShoppingCart extends \ShoppingCart
{
    /**
     * Finds an existing order item.
     *
     * This method is originally responsible for ensuring unique order items are created correctly
     *
     * However, if we want to check for simple options, we have to do some extra checks
     *
     *
     * @param \Buyable $buyable
     * @param array    $customfilter
     *
     * @return \OrderItem | bool (the item requested, or false)
     */
    public function get(\Buyable $buyable, $customfilter = array())
    {
        $order = $this->current();
        if (!$buyable || !$order) {
            return false;
        }

        $buyable = $this->getCorrectBuyable($buyable);

        $filter = array(
            'OrderID' => $order->ID,
        );
        $itemclass = \Config::inst()->get(get_class($buyable), 'order_item');
        $relationship = \Config::inst()->get($itemclass, 'buyable_relationship');
        $filter[$relationship . "ID"] = $buyable->ID;
        $required = array('Order', $relationship);
        if (is_array($itemclass::config()->required_fields)) {
            $required = array_merge($required, $itemclass::config()->required_fields);
        }
        $query = new \MatchObjectFilter($itemclass, array_merge($customfilter, $filter), $required);

        // ==== From here on the code might be different from default class

        $items = $itemclass::get()->where($query->getFilter());

        $item = $items->first();

        if (!$item) {
            return $this->error(_t("ShoppingCart.ItemNotFound", "Item not found."));
        }

        // do check if buyable even has module or options, else no point in doing more code
        if (!$buyable->hasExtension("Silvershop\SimpleOptions\Extensions\ProductOptionsExtension") || !$buyable->ProductOptions()->exists()) {
            return $item;
        }

        // might be bool on cart creation..
        if (!$item instanceof \OrderItem) {
            return $item;
        }

        return $this->handleItemsOptionsData($items, $customfilter);
    }

    protected function handleItemsOptionsData($items, $customfilter)
    {
        $item = $items->first();

        $submittedOptions = array_column(OrderExtension::filterDataForSimpleOptions($customfilter), "ValueID");
        sort($submittedOptions);

        // default behavior just selects items with correct ProductID, there might be multiple
        // so check if an item exists with the same options as submitted options
        // If so, that item should be selected
        foreach($items as $orderitem){
            $values = $orderitem->OrderOptionDataItems()->column("SimpleProductOptionValueID");
            sort($values);
            // compare options
            if($submittedOptions == $values){
                $item = $orderitem;
                break;
            }
        }

        // if it has no options set, then whatever
        if (!$item->OrderOptionDataItems()->exists()) {
            return $item;
        }

        $existingValues = $item->OrderOptionDataItems()->column("SimpleProductOptionValueID");

        sort($existingValues);

        // if the submitted options are the same as existing attached options, then just return the item
        // if submitted options are different, please create a new order item
        if ($submittedOptions != $existingValues) {
            return $this->error(_t("ShoppingCart.ItemNotFound", "Item not found."));
        }

        return $item;
    }
}