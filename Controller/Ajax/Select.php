<?php

namespace Magento\RuchShip\Controller\Ajax;

/**
 * Controller for ajax action called from ruch.js in checkout
 */
class Select extends \Magento\Framework\App\Action\Action
{

	protected $_pageFactory;
	protected $helper;
	
	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Framework\View\Result\PageFactory $pageFactory,
			\Magento\RuchShip\Helper\Api $helper
		)
	{
		$this->helper = $helper;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}
	
	/**
	 * Save selected Ruch point data to quote
	 */
	public function execute() {
		$inp = file_get_contents('php://input');
		$json = json_decode($inp, true);
		if(isset($json['id'])) {
    		$id = $json['p'];
    		$typ = $json['t'];
    		$desc = array('r' => $json['r'], 'c' => $json['c'], 'a' => substr($json['a'], 0, 55));
    		
    		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    		$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
    		$q = $cart->getQuote();
    		$q->setData('ruch_point', $id);
    		$q->setData('ruch_type', $typ);
    		$q->setData('ruch_desc', json_encode($desc));
    		$q->setData('ruch_destinationcode', $json['id']);
    		$q->save();
		}

		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(json_encode(array('status' => 1)));
		$this->getResponse()->sendResponse();
		exit;
	}
	
}
