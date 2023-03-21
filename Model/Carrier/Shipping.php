<?php

namespace Magento\RuchShip\Model\Carrier;

/**
 * Main plugin class
 */
class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
	protected $typy0 = array(0 => 'Zwykła', 1 => 'Pobraniowa');
	protected $typy1 = array(1 => 'Pobraniowa', 0 => 'Zwykła');
	protected $dozwolone = array(95 => 1);
	protected $_code = 'ruch';
	protected $_rateResultFactory;
	protected $_rateMethodFactory;
	protected $helper;
	protected $_paymentModelConfig;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
		\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
		\Magento\RuchShip\Helper\Api $helper,
	    \Magento\Payment\Model\Config $paymentModelConfig,
		array $data = []
	) {
		$this->_rateResultFactory = $rateResultFactory;
		$this->_rateMethodFactory = $rateMethodFactory;
		$this->helper = $helper;
		$this->_paymentModelConfig = $paymentModelConfig;
		parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
	}

	/**
	 * Magento carrier interface function
	 * Collects my all possible methods and returns to Magento as Magento object
	 */
	public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request) {
		if (!$this->getConfigFlag('active')) {
			return false;
		}
		
		$payments = $this->_paymentModelConfig->getActiveMethods();
		$result = $this->_rateResultFactory->create();
		$result->append($this->meth2rate(array('ID' => 95, 'Name' => 'ORLEN Paczka'), 0, $request));
		if(array_key_exists('cashondelivery', $payments) && $this->getConfigFlag('codActive')) $result->append($this->meth2rate(array('ID' => 95, 'Name' => 'ORLEN Paczka'), 1, $request));
		return $result;
	}

	/**
	 * Helper function - converts Ruch method parameters to Magento object
	 */
	public function meth2rate($m, $pobranie, \Magento\Quote\Model\Quote\Address\RateRequest $request) {
        $rate = $this->_rateMethodFactory->create();
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $kod = $m['ID'] . '_' . $pobranie;
        $nazwa = $m['Name'];
        if($this->getConfigData('name' . $m['ID']) != '') $nazwa = $this->getConfigData('name' . $m['ID']);
        $rate->setMethod($kod);
        $rate->setMethodTitle($nazwa . ($pobranie ? ' - płatność przy odbiorze' : ''));
        if($request->getFreeShipping()) {
            $rate->setPrice(0);
            $rate->setCost(0);
        }
        else {
            if($pobranie) $cena = $this->getConfigData('priceCOD');
            else $cena = $this->getConfigData('price');
            $rate->setPrice($cena);
            $rate->setCost($cena);
        }
        return $rate;
	}

	/**
	 * Magento carrier interface function
	 * Collects my all possible methods and returns to Magento
	 */
	public function getAllowedMethods() {
		return [$this->_code => $this->getConfigData('name'), $this->_code . '_cod' => $this->getConfigData('name') . ' - COD'];
	}
		
	/**
	 * Magento carrier interface function
	 * Returns true because we are providing label generation
	 */
	public function isShippingLabelsAvailable() {
        return true;
    }

    /**
     * Magento carrier interface function
     * Returns Magento object containing label and tracking number for given request using Ruch API to get the label
     */
    public function requestToShipment($request) {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
        	throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = array();
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new \Magento\Framework\DataObject($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->helper->shipmentRequestApi($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent()
                );
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new \Magento\Framework\DataObject(array(
            'info'   => $data
        ));
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }
        return $response;
	}

	/**
	 * Magento carrier interface function
	 * Returns true because we are providing tracking info
	 */
	public function isTrackingAvailable() {
		return true;
	}

	/**
	 * Magento carrier interface function
	 * Returns Magento object containing tracking data for given request using Ruch API
	 */
	public function getTrackingInfo($tracking)
    {
    	$result = $this->helper->getTrackingApi($tracking);
    
    	if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
    		$trackings = $result->getAllTrackings();
    		if ($trackings) {
    			return $trackings[0];
    		}
    	} elseif (is_string($result) && !empty($result)) {
    		return $result;
    	}
    
    	return false;
    }
    
    /**
     * Called when error occured in requestToShipment
     */
    public function rollBack($data)
    {
        return true;
    }

    /**
     * Magento carrier interface function
     * Returns my allowed containers for user interface in admin panel
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
    	return ['Paczka'];
    }
    
}
