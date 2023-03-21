<?php

namespace Magento\RuchShip\Block;

use Magento\Framework\Data\OptionSourceInterface;

class PointTypeOptions implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Wszystkie')],
            ['value' => '1', 'label' => __('Punkt Sprzedaży Detalicznej')],
            ['value' => '2', 'label' => __('APM')],
            ['value' => '3', 'label' => __('PKN')],
            ['value' => '4', 'label' => __('Punkt Sprzedaży Prasy')],
            ['value' => '5', 'label' => __('PSF')],
            ['value' => '6', 'label' => __('Punkt pozaprasowy')],
        ];
    }
}