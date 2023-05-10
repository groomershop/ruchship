<?php

namespace Magento\RuchShip\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Measure\Weight;
use \Magento\Framework\Measure\Length;

/**
 * Helper class providing interface to Ruch API
 */
class Api extends AbstractHelper
{
	protected $scopeConfig;
	protected $logger;
	private $filesystem;
	private $trackFactory;
	private $trackErrorFactory;
	private $trackStatusFactory;
	private $fn = 'pwr_list.json';
	static private $metoda = 95;
	static private $metoda_mini = 122;
	static private $metoda_apm = 127;
	static private $ruch_testurl = 'http://api.ruch.opennet.pl/api.asmx?WSDL';
	static private $ruch_produrl = 'https://apixml.paczkawruchu.pl/api.asmx?WSDL';
	
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Directory\Model\CurrencyFactory $currencyFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
		\Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
		\Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
		\Magento\Framework\Filesystem $filesystem
	)
	{
		$this->scopeConfig = $scopeConfig;
		$this->logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
		$this->filesystem = $filesystem;
		$this->trackFactory = $trackFactory;
		$this->trackErrorFactory = $trackErrorFactory;
		$this->trackStatusFactory = $trackStatusFactory;		
		return parent::__construct($context);
	}

    /**
     * Create shipment in API and get label for it, return as Magento DataObject
     */
	public function shipmentRequestApi($r) {
		$sid = $r->getStoreId();
		$p = $r->getPackageParams();
		$waga = $p->getWeight();
		$wys = $p->getHeight();
		$szer = $p->getWidth();
		$dlug = $p->getLength();
		$kwota = round($r->getOrderShipment()->getOrder()->getGrandTotal(), 2);
		$tpl = $_POST['packages']['1']['params']['ruch_tpl'];
		$rserv = $_POST['packages']['1']['params']['ruch_serv'];
		
		$q_id = $r->getOrderShipment()->getOrder()->getQuoteId();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
		$q = $quoteFactory->create()->load($q_id);
	
		$weightUnits = $p->getWeightUnits() == Weight::POUND ? 'LBS' : 'KGS';
		$dimensionsUnits = $p->getDimensionUnits() == Length::INCH ? 'IN' : 'CM';
	
		if($weightUnits == 'LBS') {
			$waga = $waga * 0.45359237;
			$waga = ceil($waga);
		}
		if($dimensionsUnits == 'IN') {
			$wys = $wys * 2.54;
			$wys = ceil($wys);
			$szer = $szer * 2.54;
			$szer = ceil($szer);
			$dlug = $dlug * 2.54;
			$dlug = ceil($dlug);
		}
	
		if(!$waga) $waga = 1;
		if(!$wys) $wys = $this->getConfigData('DefaultHeight');
		if(!$wys) $wys = 10;
		if(!$szer) $szer = $this->getConfigData('DefaultWidth');
		if(!$szer) $szer = 10;
		if(!$dlug) $dlug = $this->getConfigData('DefaultDeep');
		if(!$dlug) $dlug = 10;
	
		$tmp = explode('_', $r->getShippingMethod());
		$typ = $q->getData('ruch_type');
		$metoda = self::$metoda;
		$packtype = 'Package';
		if($typ == 'A') {
		    $metoda = self::$metoda_apm;
		    $packtype = $tpl;
		}
		elseif($rserv == 'M') {
		    $metoda = self::$metoda_mini;
		}
		if($tmp[1] == 2) $pobranie = $kwota;
		else $pobranie = 0;
		$point = $q->getData('ruch_point');
		$this->_log('cs q=' . $q->getId());
		$scp = explode(' ', $r->getShipperContactPersonName(), 2);
		$rcp = explode(' ', $r->getRecipientContactPersonName(), 2);
		$params = array(
				"token" => array(
						"UserName" => $this->getConfigData('apiID'),
						"Password" => $this->getConfigData('apiPass')
				),
				"ShipmentRequest" => array(
						"ServiceId" => $metoda,
						"ShipFrom" => array(
								"Name" => trim($r->getShipperContactCompanyName()),
								"Street" => $r->getShipperAddressStreet(),
								"HouseNumber" => '.',
								"Local" => '.',
								"City" => $r->getShipperAddressCity(),
								"PostCode" => $r->getShipperAddressPostalCode(),
								"CountryCode" => $r->getShipperAddressCountryCode(),
    						    "PersonName" => $scp[0] != '' ? $scp[0] : '-',
    						    "PersonSurname" => $scp[1] != '' ? $scp[1] : '.',
						        "Contact" => $r->getShipperContactPhoneNumber(),
								"Email" => $r->getShipperEmail(),
						),
						"ShipTo" => array(
    						    "Name" => trim($r->getRecipientContactCompanyName()),
								"Street" => $r->getRecipientAddressStreet(),
								"HouseNumber" => '.',
								"Local" => '.',
								"City" => $r->getRecipientAddressCity(),
								"PostCode" => $r->getRecipientAddressPostalCode(),
								"CountryCode" => $r->getRecipientAddressCountryCode(),
    						    "PersonName" => $rcp[0] != '' ? $rcp[0] : '-',
    						    "PersonSurname" => $rcp[1] != '' ? $rcp[1] : '.',
						        "Contact" => $r->getRecipientContactPhoneNumber(),
								"Email" => $r->getRecipientEmail(),
						),
						"ParcelLocker" => array(
								"PointId" => $point
						),
						"Parcels" => array(
								"Parcel"=>array(
										"Type" => $packtype,
										"Weight" => $waga,
										"D" => $dlug,
										"W" => $wys,
										"S" => $szer
								)
						),
						"InsuranceAmount" => ($pobranie != 0 ? $pobranie : $this->getConfigData('wartoscUbezpieczenia')),
						"MPK" => "",
    				    "ContentDescription" => '',
    				    "ReferenceNumber" => $r->getOrderShipment()->getOrder()->getIncrementId(),
						"rabateCoupon" => 0,
						"LabelFormat" => 'PDF',
						"AdditionalServices" => array()
				)
		);
		if($pobranie) {
			$params['ShipmentRequest']['COD'] = array(
					"Amount" => $pobranie,
					"RetAccountNo" => 0
			);
		}
		if($this->getConfigData('sendMail')) $params['ShipmentRequest']['AdditionalServices']['AdditionalService'][] = array('Code' => 'EMAIL');
		if($this->getConfigData('sendSms')) $params['ShipmentRequest']['AdditionalServices']['AdditionalService'][] = array('Code' => 'SMS');
	
		$result = new \Magento\Framework\DataObject();
		try {
			$response = $this->callWs("CreateShipment", $params);
		} catch(Exception $e) {
			$this->_log('CreateShipment err1 ' . $e->getMessage(), true);
			$result->setErrors($e->getMessage());
			return $result;
		}
		if(!isset($response->CreateShipmentResult->responseDescription) || ($response->CreateShipmentResult->responseDescription != 'Success')) {
		    if(($response->CreateShipmentResult->responseCode == 1033) && ($point == 0)) $result->setErrors('Brak ID punktu w zamówieniu');
			else $result->setErrors('[' . $response->CreateShipmentResult->responseCode . '] ' . $response->CreateShipmentResult->responseDescription);
			$this->_log('CreateShipment err2 ' . print_r($response, true), true);
			return $result;
		}
		$shippingLabelContent = $response->CreateShipmentResult->ParcelData->Label->MimeData;
		$response->CreateShipmentResult->ParcelData->Label->MimeData = '[' . strlen($shippingLabelContent) . ']';
		$this->_log('CreateShipment r ' . print_r($response, true), true);
		$result->setShippingLabelContent($shippingLabelContent);
		$result->setTrackingNumber($this->getTrack($r, $response->CreateShipmentResult->PackageNo));
	
		return $result;
	}
	
    /**
     * Get tracking info for given number and return it as a Magento object
     */
	public function getTrackingApi($trackings) {
	
		$params = array(
				"token" => array(
						"UserName" => $this->getConfigData('apiID'),
						"Password" => $this->getConfigData('apiPass')
				),
				"packageNo" => $trackings
		);
		$this->_log('GetTracking q ' . print_r($params, true), true);
	
		try {
			$response = $this->callWs("GetTracking", $params);
		} catch(Exception $e) {
			$result = $this->trackErrorFactory->create();
			$this->_log('GetTracking err ' . $e->getMessage(), true);
			$result->setErrors($e->getMessage());
			return $result;
		}
		$this->_log('GetTracking r ' . print_r($response, true), true);
	
		$result = $this->trackFactory->create();
		$status = $this->trackStatusFactory->create();
		$status->setCarrier('ruch');
		$status->setCarrierTitle($this->getConfigData('title'));
		$status->setTracking($trackings);
		$status->setPopup(0);
		//$status->setUrl("http://www.ruch.pl/blablabla?num=$trackings");	// Use it if we need return external tracking url
	
		$resultArr = array();
		$resultArr['status'] = $response->GetTrackingResult->CurrentStatus->Description;
		if($response->GetTrackingResult->DateDelivered) {
			$d = new \DateTime($response->GetTrackingResult->DateDelivered);
			$resultArr['deliverydate'] = $d->format('Y-m-d');
			$resultArr['deliverytime'] = $d->format('G:i:s');
		}
	
		$packageProgress = array();
		if(isset($response->GetTrackingResult->Status->OrderStatus)) {
			if(is_object($response->GetTrackingResult->Status->OrderStatus)) $response->GetTrackingResult->Status->OrderStatus = array($response->GetTrackingResult->Status->OrderStatus); 
			foreach ($response->GetTrackingResult->Status->OrderStatus as $event) {
				$tempArr = array();
				$tempArr['activity'] = $event->Description;
				$d = new \DateTime($event->EventTimestamp);
				$tempArr['deliverydate'] = $d->format('Y-m-d');
				$tempArr['deliverytime'] = $d->format('G:i:s');
				if(isset($event->EventParam)) $tempArr['deliverylocation'] = $event->EventParam;
	
				$packageProgress[] = $tempArr;
			}
		}
		$resultArr['progressdetail'] = $packageProgress;
	
		$status->addData($resultArr);
		$result->append($status);
		return $result;
	}
	
    /**
     * Get tracking number from given package number
     */
	private function getTrack($r, $num) {
		return $num;
	}

	/**
	 * Helper function - call Ruch API function using PHP Soap, returns result from API as a PHP object/array structure
	 */
	function callWs($func, $req) {
		if(!extension_loaded('soap')) return 'SOAP extension not loaded';
		$url = $this->getConfigData('sandbox') == '1' ? self::$ruch_testurl : self::$ruch_produrl;
		$this->_log('ws url=' . $url . ' req=' . print_r($req, true), true);
		$client = new \SoapClient($url);
		$response = $client->__soapCall($func, array($req));
		return $response;
	}
	
	/**
	 * Helper function - get my config data
	 */
	private function getConfigData($k) {
    	return $this->scopeConfig->getValue('carriers/ruch/' . $k);
    }

    /**
     * Helper function - log to var/log/system.log file
     */
    private function _log($text, $info = false)
    {
    	if($info) $this->logger->info('Ruch ' . $text);
    	else $this->logger->error('Ruch ' . $text);
    }
    
}

?>
