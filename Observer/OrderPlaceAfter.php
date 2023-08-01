<?php

namespace Magento\RuchShip\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Observer called when order is created after successfuly completed checkout
 * checkout_onepage_controller_success_action in frontend/events.xml
 */
class OrderPlaceAfter implements ObserverInterface
{

    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }
   
    /**
     * Copy my data from quote to order object
     */
	public function execute(Observer $observer) {
	    if(!$this->scopeConfig->getValue('carriers/ruch/active')) return;
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
	    $ids = $observer->getEvent()->getOrderIds();
	    foreach($ids as $id){
	        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($id);
	        $q_id = $order->getQuoteId();
	        $q = $quoteFactory->create()->load($q_id);
		$comment = __('ORLEN Paczka: %1', $q->getData('ruch_destinationcode'));
		$order->addStatusHistoryComment($comment)
                    ->setIsCustomerNotified(false)
                    ->setIsVisibleOnFront(true);
	        $order->save();
	    }
	}

}
