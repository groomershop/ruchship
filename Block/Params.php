<?php

namespace Magento\RuchShip\Block;

/**
 * Block providing Ruch parameters in checkout page
 */
class Params extends \Magento\Framework\View\Element\Template
{	
	protected $scopeConfig;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{		
		$this->scopeConfig = $scopeConfig;
		parent::__construct($context);
	}

	public function isTest()
	{
	    return $this->scopeConfig->getValue('carriers/ruch/sandbox');
	}
	
	public function isActive()
	{
		return $this->scopeConfig->getValue('carriers/ruch/active');
	}

    public function showDeliveryFilter()
    {
        return $this->scopeConfig->getValue('carriers/ruch/showDeliveryFilter');
    }

    public function getDeliveryFilterValue()
    {
        return $this->scopeConfig->getValue('carriers/ruch/deliveryFilterValue');
    }

    public function showPointTypeFilter()
    {
        return $this->scopeConfig->getValue('carriers/ruch/showPointTypeFilter');
    }

    public function getPointTypeFilterValue()
    {
        $pointTypeFilterValue = $this->scopeConfig->getValue('carriers/ruch/pointTypeFilterValue');
        switch ($pointTypeFilterValue) {
            case 1:
                return 'PSD';
            case 2:
                return 'APM';
            case 3:
                return 'PKN';
            case 4:
                return 'PSP';
            case 5:
                return 'PSF';
            case 6:
                return 'PPP';
            default:
                return 'ALLTYPE';
        }
    }
}

?>