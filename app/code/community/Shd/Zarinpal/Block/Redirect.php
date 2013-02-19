<?php
/**
 * Magento
 * @category   Payment
 * @package    Shd_Zarinpal
 * @copyright  Copyright (c) 2013 Shayan Davarzani (shayandavarzani@gmail.com)
 * @see https://github.com/shayand
 */
class Shd_Zarinpal_Block_Redirect extends Mage_Core_Block_Template
{
    /**
     * Return checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return order instance
     *
     * @return Mage_Sales_Model_Order|null
     */
    protected function _getOrder()
    {
        if ($this->getOrder()) {
            return $this->getOrder();
        } elseif ($orderIncrementId = $this->_getCheckout()->getLastRealOrderId()) {
            return Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        } else {
            return null;
        }
    }

    /**
     * Get form data
     *
     * @return array
     */
    public function getFormData()
    {
        #return $this->_getOrder()->getPayment()->getMethodInstance()->getFormFields();
	$order = $this->_getOrder()->_data;
	$array = $this->_getOrder()->getPayment()->getMethodInstance()->getFormFields();
	$price = $array["price"];
        
    $seller_id = $this->_getOrder()->getPayment()->getMethodInstance()->getConfigData('seller_id');	

	$len = strlen($price);
	$len -= 2;
	$price = substr($price,0,$len);
	
	$params = array(
	 			'pin' => $seller_id ,  
                'amount' => $price,
                'orderId' => $order["entity_id"],
				'authority' => 0,
				'status' => 1
              );
	
	
	return $params;		
    }

    /**
     * Getting gateway url
     *
     * @return string
     */
    public function getFormAction()
    {
    		   
		
		$order = $this->_getOrder()->_data;
		$array = $this->_getOrder()->getPayment()->getMethodInstance()->getFormFields();
		$price = $array["price"];
			
		$seller_id = $this->_getOrder()->getPayment()->getMethodInstance()->getConfigData('seller_id');	
	
		$price = round($order["grand_total"],0);
		$price /= 10;
		
		$callBackUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$callBackUrl .= "zarinpal/processing/response/";
		
		$params = array($seller_id,
						$price,
						$callBackUrl,
						$order["entity_id"]);
		
		$client = new SoapClient('http://www.zarinpal.com/WebserviceGateway/wsdl');
		$res = $client->__soapCall('PaymentRequest',$params);
		
		if(strlen($res) == 36){
			$return = "https://www.zarinpal.com/users/pay_invoice/".$res;
		}
		return $return;
    }
}
