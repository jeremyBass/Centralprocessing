<?php
/**
 * @category   Cybersource
 * @package    Wsu_Centralprocessing
 */
class Wsu_Centralprocessing_Helper_Data extends Mage_Core_Helper_Abstract
{

    var $order_obj;


    public function getConfig($field, $default = null)
    {
        $value = Mage::getStoreConfig('payment/centralprocessing/' . $field);
        if(!isset($value) or trim($value) == ''){
            return $default;
        }else{
            return $value;
        }
    }

    public function log($data)
    {
        if(!$this->getConfig('enable_log')){
            return;
        }
        Mage::log($data, null, 'centralprocessing.log', true);
    }
	public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')  {
            return true;
        }

        return false;
    }
    public function getResponseGuidInfo($GUID,$mode)
    {
        $html="";
        if($GUID!=""){
            $fields_string="RequestGUID=".$GUID;
            $url = trim($this->getCentralprocessingUrl($mode),'/');
            $url .= DS.($this->getAuthorizationType()=="AUTHCAP"?"AuthCapResponse":"AuthCapResponse");

            $wrapper = fopen('php://temp', 'r+');

            //open connection
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $wrapper);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count(1));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            if($result === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
            //close connection
            curl_close($ch);

            $html=sprintf("<a href='#' id='showGuidInfo'>record info</a><div id='guidInfo' style='display:none;'>%s</div><script>(function($){ $(document).ready(function(){ $('#showGuidInfo').on('click',function(){ if( $('#guidInfo').is(':visible')){ $('#guidInfo').hide(); }else{ $('#guidInfo').show(); } });   }); })(jQuery);</script>",$result);
        }
        return $html;
    }


    public function makeGatewayRequest()
    {
        $helper		= Mage::helper('centralprocessing');
        $order = $helper->order_obj;
        if( !empty($order) && !is_array( $order ) ){
            $standard 	= $order->getPayment()->getMethodInstance();
            $formFields = $standard->getFormFields();
        }elseif( !empty($order) ){
            $standard 	= $order[0]->getPayment()->getMethodInstance();
            $formFields = $standard->getFormFields(true);
            Mage::register('multishippment_orders', $order);
        }

        //url-ify the data for the POST
        $fields_string="";
        $auth_type = $helper->getAuthorizationType();
        $url = trim($helper->getCentralprocessingUrl(),'/');
        $url .= DS.( "AUTHCAP" === $auth_type ? "AuthCapRequestWithAddress" : "AuthRequestWithAddress" );//AuthRequestWithCancelURL


        foreach($formFields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');


        $wrapper = fopen('php://temp', 'r+');

        //open connection
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $wrapper);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($formFields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);
        if($result === false) {
            Mage::log('Curl error: ' . curl_error($ch),Zend_Log::ERR,"bad_cc_xml_responses.txt");
            Mage::throwException('There seems to be something wrong with the output of the credit card server');
        }


        //close connection
        curl_close($ch);

        ob_start();
        var_dump($url);
        var_dump($fields_string);
        var_dump($result);
        $log = ob_get_clean();
        Mage::log($log,Zend_Log::NOTICE,"redirect-result.txt");

        if(strpos($result,'!DOCTYPE HTML')!==false){
            Mage::log($result,Zend_Log::ERR,"bad_cc_xml_responses.txt");
            Mage::throwException('There seems to be something wrong with the output of the credit card server');
        }else{
            /**/
            $nodes = new SimpleXMLElement($helper->removeResponseXMLNS($result));
            //$code = $nodes->RequestReturnCode;  // put in just in case
            $urlRedirect = (string) $nodes->WebPageURLAndGUID;
            $guid = (string) $nodes->RequestGUID;
            $state = json_decode($formFields['ApplicationStateData']);

            if(strpos($state->roid,',')!==false){
                $_orders = explode(',',$state->roid);
                foreach($_orders as $item){
                    $_order = Mage::getModel('sales/order')->load($item,'increment_id');
                    $payment = $_order->getPayment();
                    $payment->setResponseGuid($guid);
                    $payment->setCcMode($helper->getConfig('mode')>0?"live":"test");
                    $payment->setAuthType($auth_type);
                    $payment->save();
                }
            }else{
                $order = Mage::getModel('sales/order')->load($state->roid,'increment_id');
                $payment = $order->getPayment();
                $payment->setResponseGuid($guid);
                $payment->setCcMode($helper->getConfig('mode')>0?"live":"test");
                $payment->setAuthType($auth_type);
                $payment->save();
            }

            ob_start();
            var_dump($urlRedirect);
            $log = ob_get_clean();
            Mage::log($log,Zend_Log::NOTICE,"redirect.txt");
        }
        return $urlRedirect;
    }


    public function capturePreAuth($payment, $amount)
    {
        $helper = Mage::helper('centralprocessing');
        $url = trim($helper->getCentralprocessingUrl(),'/');
        $url .= DS.("CaptureRequest");
        $tran_type = Mage::getStoreConfig('payment/centralprocessing/tran_type');
        $GUID = $payment->getResponseGuid();

        //var_dump($GUID);
        //var_dump($amount);
        //var_dump($tran_type);

        $formFields = array(
            "RequestGUID"=>$GUID,
            "CaptureAmount"=>$amount,
            "OneStepTranType"=>$tran_type
        );



        $fields_string="";
        foreach($formFields as $key=>$value) {
             $fields_string .= $key.'='.$value.'&';
        }

        rtrim($fields_string, '&');

        $wrapper = fopen('php://temp', 'r+');

        //open connection
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $wrapper);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($formFields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);
        if($result === false) {
            Mage::log('Curl error: ' . curl_error($ch),Zend_Log::ERR,"bad_cc_xml_responses.txt");
            Mage::throwException('There seems to be something wrong with the output of the credit card server');
        }


        //close connection
        curl_close($ch);

        ob_start();
        var_dump($url);
        var_dump($fields_string);
        var_dump($result);
        $log = ob_get_clean();
        Mage::log($log,Zend_Log::NOTICE,"cap_proof.txt");

        //var_dump($log);
        return $result;
    }

    public function removeResponseXMLNS($input)
    {
        // Remove XML response namespaces one by one
        $input = str_replace(' xmlns="webservice.it.wsu.edu"','',$input);
        $input = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"','',$input);
        return str_replace(' xmlns:xsd="http://www.w3.org/2001/XMLSchema"','',$input);
    }

    public function getHashSign($params, $signedField = 'signed_field_names')
    {
        $signedFieldNames = (isset($params[$signedField]))?explode(",", $params[$signedField]):array();
        foreach ($signedFieldNames as &$field) {
           $dataToSign[] = $field . "=" . (isset($params[$field]))?$params[$field]:"";
        }
        $data      =  implode(",", $dataToSign);
        $secretKey = $this->getConfig('secret_key');
        $hashSign = base64_encode(hash_hmac('sha256', $data, $secretKey, true));
        return $hashSign;
    }

    public function getInfo()
    {
        $message = $this->getConfig('checkout_info');
        return $this->__($message);
    }

    public function getAuthorizationType()
    {
        return $this->getConfig('authorization_type');
    }

    public function getIssuerUrls()
    {
        return array("live" => $this->getConfig('live_hop_url'),
                     "test" => $this->getConfig('test_hop_url'));
    }

    public function getCentralprocessingUrl($mode=0)
    {
        $setIssuerUrls 	= $this->getIssuerUrls();
        if($this->getConfig('mode') || $mode==1){
            return $setIssuerUrls["live"];
        }else{
            return $setIssuerUrls["test"];
        }
    }

    public function getPostbackUrl()
    {
        return $this->getIpnUrl();
    }

    public function getReturnURL()
    {
        return Mage::getUrl( ( $this->getConfig('use_return_url') ? $this->getConfig('return_url') : 'processing/process/router' ), array('_secure' => true) );
    }

    public function getCancelUrl()
    {
        return Mage::getUrl( ( $this->getConfig('use_cancel_url') ? $this->getConfig('cancel_url') : 'processing/process/cancel' ), array('_secure' => true) );
    }

    protected function getIpnUrl()
    {
        return Mage::getUrl('processing/process/ipn', array('_secure' => true));
    }

    public function getCardType($code)
    {
        $cards=Mage::getModel('centralprocessing/system_config_source_cards_type')->toOptionArray();
        return isset($cards[$code])?$cards[$code]:"unset";
    }

    public function _getPaymentMethod($order)
    {
        return $order->getPayment()->getMethodInstance()->getCode();
    }

    public function _processOrderStatus($order,$auth_type)
    {
        $invoice = $order->prepareInvoice();

        $invoice->register();
        Mage::getModel('core/resource_transaction')
           ->addObject($invoice)
           ->addObject($invoice->getOrder())
           ->save();

        $invoice->sendEmail(true, '');
        if( "AUTHCAP" === $auth_type ){
            // this should be an optional part and configurable
            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID)->save();
        }
        $this->_changeOrderStatus($order);
        return true;
    }

    public function _changeOrderStatus($order)
    {
        $statusMessage = '';
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
    $order->save();
    }




}
