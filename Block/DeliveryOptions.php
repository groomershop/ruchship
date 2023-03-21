<?php

namespace Magento\RuchShip\Block;

use Magento\Framework\Data\OptionSourceInterface;

class DeliveryOptions implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Wszystkie')],
            ['value' => '1', 'label' => __('Prasa')],
            ['value' => '2', 'label' => __('Kurier')],
        ];
    }
}