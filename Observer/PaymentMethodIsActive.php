<?php

namespace Magento\RuchShip\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Checkout - filtering available payment methods based on selected shipment method
 */
class PaymentMethodIsActive implements ObserverInterface
{

	protected $scopeConfig;
	protected $cart;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Cart $cart
	)
	{
		$this->scopeConfig = $scopeConfig;
		$this->cart = $cart;
	}
	
    /**
     * Left only COD payment if COD Ruch method is selected, only non-COD when non-COD Ruch selected
     */
	public function execute(Observer $observer) {
		$event = $observer->getEvent();
		$method = $event->getMethodInstance();
		$result = $event->getResult();
		$carriers = $this->cart->getQuote()->getShippingAddress()->getShippingMethod();
		$akt = $this->scopeConfig->getValue('carriers/ruch/active') && $this->scopeConfig->getValue('carriers/ruch/filter');
		if(!$akt) {	// Plugin inactive or inactive filtering in config
		}
		else {
			$tmp = explode('_', $carriers);
				
			if($tmp[0] == 'ruch') { // Ruch
				if(!(($tmp[2] == 0) xor ($method->getCode() == 'cashondelivery'))) $result->setData('is_available', false);
			}
		}
	}

}
