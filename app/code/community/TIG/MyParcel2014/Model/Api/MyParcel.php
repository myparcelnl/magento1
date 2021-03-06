<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 *
 * Myparcel API class. Contains all the functionality to connect to Myparcel and get information or create consignments
 *
 * @method bool hasStoreId()
 */
class TIG_MyParcel2014_Model_Api_MyParcel extends Varien_Object
{
    /**
     * Supported request types.
     */
    const REQUEST_TYPE_CREATE_CONSIGNMENT   = 'shipments';
    const REQUEST_TYPE_REGISTER_CONFIG      = 'register-config';
    const REQUEST_TYPE_SETUP_LABEL          = 'v2/shipment_labels';
    const REQUEST_TYPE_RETRIEVE_LABEL       = 'shipment_labels';
    const REQUEST_TYPE_RETRIEVE_V2_LABEL    = 'pdfs';
    const REQUEST_TYPE_GET_LOCATIONS        = 'pickup';

    /**
     * Consignment types
     */
    const TYPE_MORNING             = 1;
    const TYPE_STANDARD            = 2;
    const TYPE_NIGHT               = 3;
    const TYPE_RETAIL              = 4;
    const TYPE_RETAIL_EXPRESS      = 5;

    /**
     * API headers
     */
    const REQUEST_HEADER_SHIPMENT           = 'Content-Type: application/vnd.shipment+json; ';
    const REQUEST_HEADER_RETURN             = 'Content-Type: application/vnd.return_shipment+json; ';
    const REQUEST_HEADER_UNRELATED_RETURN   = 'Content-Type: application/vnd.unrelated_return_shipment+json; ';

    /**
     * Shipment v2 endpoint active from x number of orders
     */
    const SHIPMENT_V2_ACTIVE_FROM = 25;
    const MAX_STREET_LENGTH = 40;

    /**
     * @var string
     */
    protected $apiUsername = '';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $apiUrl = '';

    /**
     * @var string
     */
    protected $requestString = '';

    /**
     * @var string
     */
    protected $requestType = '';

    /**
     * @var string
     */
    protected $requestHeader = '';

    /**
     * @var string
     */
    protected $requestResult = false;

    /**
     * @var string
     */
    protected $requestError = false;

    /**
     * @var string
     */
    protected $requestErrorDetail = false;

    /**
     * @var string
     */
    private $labelDownloadUrl = null;

    /**
     * sets the api username and api key on construct.
     *
     * @return void
     */
    protected function _construct()
    {
        $storeId  = $this->getStoreId();
        $helper   = Mage::helper('tig_myparcel');
        $username = $helper->getConfig('username', 'api', $storeId);
        $key      = $helper->getConfig('key', 'api', $storeId, true);
        $url      = $helper->getConfig('url');

        if (Mage::app()->getStore()->isCurrentlySecure()) {
            if(!Mage::getStoreConfig('tig_myparcel/general/ssl_handshake')){
                $url = str_replace('http://', 'https://', $url);
            }
        }

        if (empty($username) && empty($key)) {
            return;
        }

        $this->apiUrl      = $url;
        $this->apiUsername = $username;
        $this->apiKey      = $key;
    }

    /**
     * Get label url from v2 endpoint
     *
     * @return string
     */
    public function getLabelDownloadUrl()
    {
        return $this->labelDownloadUrl;
    }

    /**
     * @param string $labelDownloadUrl
     */
    public function setLabelDownloadUrl($labelDownloadUrl)
    {
        $this->labelDownloadUrl = $labelDownloadUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return int
     * @throws \Mage_Core_Model_Store_Exception
     */
    public function getStoreId()
    {
        if ($this->hasStoreId()) {
            return $this->_getData('store_id');
        }

        $storeId = Mage::app()->getStore()->getId();

        $this->setStoreId($storeId);
        return $storeId;
    }

    public function setStoreId($storeId)
    {
        $helper = Mage::helper('tig_myparcel');

        $this->storeId     = $storeId;
        $this->apiUsername = $helper->getConfig('username', 'api', $storeId);
        $this->apiKey      = $helper->getConfig('key', 'api', $storeId, true);

        return $this;
    }

    /**
     * returns the response as an array, when an error occurs it will return the error message as a string
     * @return array
     */
    public function getRequestResponse()
    {
        if(!empty($this->requestError)){
            return $this->requestError;
        }

        return $this->requestResult;
    }

    public function getRequestErrorDetail()
    {
        $errorDetail = $this->requestErrorDetail;

        if(!$errorDetail){

            if(!empty($this->requestError)){
                return $this->requestError;
            }

            return false;
        }

        if(is_string($errorDetail))
        {
            return $errorDetail;
        }

        if(is_array($errorDetail) && !empty($errorDetail))
        {
            $return = $this->requestError.' - ';
            foreach($errorDetail as $key => $errorMessage)
            {
                $return .= $key;
                if(is_string($errorMessage))
                {
                    $return .= ': '.$errorMessage;
                }

                if(is_array($errorMessage) && !empty($errorMessage))
                {
                    $return .= ':<br/>'."\n";
                    foreach($errorMessage as $messageKey => $value)
                    {
                        $return .= $messageKey .' - '.$value[0];
                    }
                }
            }

            if($return == '')
            {
                return false;
            }

            return $return;
        }
        return false;
    }

    /**
     * Sets the parameters for an API call based on a string with all required request parameters and the requested API
     * method.
     *
     * @param string $requestString
     * @param string $requestType
     * @param string $requestHeader
     *
     * @return $this
     */
    protected function _setRequestParameters($requestString, $requestType, $requestHeader = '')
    {
        $this->requestString = $requestString;
        $this->requestType   = $requestType;

        $header[] = $requestHeader . 'charset=utf-8;version=1.1';
        $header[] = 'Authorization: basic ' . base64_encode($this->apiKey);
        $header[] = 'User-Agent:'. $this->_getUserAgent();

        $this->requestHeader   = $header;

        return $this;
    }

    /**
     * Get the Magento version and MyParcel version
     *
     * @return string
     */
    protected function _getUserAgent()
    {
        //Get Magento and MyParcel versions
        $userAgents = [
            'Magento/'. Mage::getVersion(),
            'MyParcel-Magento/'. (string) Mage::getConfig()->getModuleConfig("TIG_MyParcel2014")->version
        ];

        $userAgent = implode(' ', $userAgents);

        return $userAgent;
    }

    /**
     * send the created request to MyParcel
     *
     * @param string $method
     *
     * @param bool $checkConfig
     * @return $this|array|false|string
     * @throws TIG_MyParcel2014_Exception
     */
    public function sendRequest($method = 'POST', $checkConfig = true)
    {
        if (!$this->_checkConfigForRequest() && $checkConfig) {
            return false;
        }

        //instantiate the helper
        $helper = Mage::helper('tig_myparcel');

        //curl options
        $options = array(
            CURLOPT_POST           => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
        );

        $config = array(
            'header'  => 0,
            'timeout' => 60,
        );

        //instantiate the curl adapter
        $request = new TIG_MyParcel2014_Model_Api_Curl();
        //add the options
        foreach($options as $option => $value)
        {
            $request->addOption($option, $value);
        }

        $header = $this->requestHeader;

        //do the curl request
        if($method == 'POST'){

            //curl request string
            $body = $this->requestString;

            //complete request url
            $url = $this->apiUrl . $this->requestType;

            // log the request url
            $helper->log($url);
            $helper->log(json_decode($body));
            $request->setConfig($config)
                ->write(Zend_Http_Client::POST, $url, '1.1', $header, $body);
        } else {

            //complete request url
            $url  = $this->apiUrl;
            $url .= $this->requestType;
            $url .= $this->requestString;

            // log the request url
            $helper->log($url);

            $request->setConfig($config)
                ->write(Zend_Http_Client::GET, $url, '1.1', $header);
        }

        //read the response
        $response = $request->read();
        $aResult = json_decode($response, true);

        if ($this->requestType == self::REQUEST_TYPE_SETUP_LABEL) {
            if (isset($aResult['data']['pdf']['url'])){
                $pdfUrl = $aResult['data']['pdf']['url'];
                $pdfUrl = str_replace('pdfs/', '', $pdfUrl);
                $pdfUrl = $this->apiUrl . self::REQUEST_TYPE_RETRIEVE_V2_LABEL . $pdfUrl;
                $this->setLabelDownloadUrl($pdfUrl);

            } else {
                $pdfError = $helper->__('There was an error when set up a PDF. Please feel free to contact MyParcel.');
                throw new TIG_MyParcel2014_Exception(
                    $pdfError . '::' . $url,
                    'MYPA-0101'
                );
            }
        }

        if(is_array($aResult)){

            //log the response
            $helper->log(json_encode($aResult, true));

            //check if there are curl-errors
            if ($response === false) {
                $error              = $request->getError();
                $this->requestError = $error;
                //$this->requestErrorDetail = $error;
                return $this;
            }

            //check if the response has errors codes
            if(isset($aResult['errors']) && isset($aResult['message'])) {
                if(strpos($aResult['message'], 'Access Denied, token is not active.') !== null){
                    $this->requestError = $helper->__('Wrong API key. Go to MyParcel settings to set the API key.');
                } else {
                    foreach ($aResult['errors'] as $tmpError) {
                        $errorMessage = $aResult['message'] . '; ' . $tmpError['fields'][0];
                        $this->requestError = $errorMessage;
                    }
                }
                $request->close();

                return $this;
            } else if (isset($aResult['errors'][0]['code'])){
                $this->requestError = $aResult['errors'][0]['code'] . ' - ' . $aResult['errors'][0]['human'][0];
                $this->requestErrorDetail = $aResult['errors'][0]['code'] . ' - ' . $aResult['errors'][0]['human'][0];
                $request->close();

                return $this;
            }
        }

        $this->requestResult = $response;

        //close the server connection with MyParcel
        $request->close();

        return $this;
    }

    /**
     * Prepares the API for processing a create consignment request.
     *
     * @param TIG_MyParcel2014_Model_Shipment $myParcelShipment
     *
     * @return $this
     * @throws \TIG_MyParcel2014_Exception
     */
    public function createConsignmentRequest(TIG_MyParcel2014_Model_Shipment $myParcelShipment)
    {
        $data = $this->_getConsignmentData($myParcelShipment);

        $requestString = $this->_createRequestString($data);

        $this->_setRequestParameters($requestString, self::REQUEST_TYPE_CREATE_CONSIGNMENT, self::REQUEST_HEADER_SHIPMENT);

        return $this;
    }

    /**
     * @param array $consignmentIds
     *
     * @return array $responseShipments|false
     * @throws \TIG_MyParcel2014_Exception
     */
    public function getConsignmentsInfoData($consignmentIds = array()){
        if($consignmentIds){
            /** @var \TIG_MyParcel2014_Model_Api_MyParcel $apiInfo */
            $responseData = $this->createConsignmentsInfoRequest($consignmentIds)
                ->sendRequest('GET')
                ->getRequestResponse();
            $responseData = json_decode($responseData);

            if (!key_exists('data', (array)$responseData)) {
                // if use filter
                return false;
            }

            $responseShipments = $responseData->data->shipments;

            return $responseShipments;

        } else {
            return false;
        }
    }

    /**
     * @param array $consignmentIds
     *
     * @return self
     */
    public function createConsignmentsInfoRequest($consignmentIds = array()){

        $requestString = '/' . implode(';',$consignmentIds) . '?size=800';

        $this->_setRequestParameters($requestString, self::REQUEST_TYPE_CREATE_CONSIGNMENT, self::REQUEST_HEADER_SHIPMENT);

        return $this;
    }

    /**
     * Prepares the API for retrieving pdf's for an array of consignment IDs.
     *
     * @param array       $consignmentIds
     * @param int|string  $start
     * @param string      $perpage
     *
     * @return $this
     */
    public function createSetupPdfsRequest($consignmentIds = array(), $start = 1, $perpage = 'A4')
    {
        $positions = '';

        if($perpage == 'A4') {
            $positions = '&positions=' . $this->_getPositions((int) $start);
        }

        $data = implode(';', $consignmentIds);
        $getParam = '/' . $data . '?format=' . $perpage . $positions;

        if ($this->useShipmentV2(count($consignmentIds))) {
            $this->_setRequestParameters($getParam, self::REQUEST_TYPE_SETUP_LABEL);
        } else {
            $this->_setRequestParameters($getParam, self::REQUEST_TYPE_RETRIEVE_LABEL);
        }

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function createFileExistsRequest($url)
    {
        $helper = Mage::helper('tig_myparcel');
        $this->setApiUrl($url);
        $this->setApiKey($helper->getConfig('key', 'api'));

        return $this;
    }

    /**
     * Prepares the API for retrieving pdf's for a consignment ID.
     *
     * @return $this
     */
    public function createRegisterConfigRequest()
    {
        $data = array(
            'webshop_version' => 'Magento ' . Mage::getVersion(),
            'plugin_version'  => (string) Mage::getConfig()->getModuleConfig('TIG_MyParcel2014')->version,
            'php_version'     => phpversion(),
        );

        $requestString = $this->_createRequestString($data);

        $this->_setRequestParameters($requestString, self::REQUEST_TYPE_REGISTER_CONFIG);

        return $this;
    }

    /**
     * Send email with return label
     *
     * @param $data array
     *
     * @return $this
     */
    public function sendUnrelatedRetourmailRequest($data)
    {
        $requestString = $this->_createRequestString($data, 'return_shipments');

        $this->_setRequestParameters($requestString, self::REQUEST_TYPE_CREATE_CONSIGNMENT, self::REQUEST_HEADER_UNRELATED_RETURN);

        return $this;
    }

    /**
     * create a request string for generating a retour-url
     *
     * @param $consignmentId
     * @return $this
     * @var Mage_Sales_Model_Order_Shipment $shipment
     */
    public function createRetourmailRequest($shipment, $consignmentId)
    {
        $data = array(
            'parent' => (int)$consignmentId,
            'carrier' => 1,
            'email' => $shipment->getOrder()->getCustomerEmail(),
            'name' => $shipment->getOrder()->getCustomerName()
        );

        $requestString = $this->_createRequestString($data, 'return_shipments');

        $this->_setRequestParameters($requestString, self::REQUEST_TYPE_CREATE_CONSIGNMENT, self::REQUEST_HEADER_RETURN);

        return $this;
    }

    /**
     * create a request string for generating a retour-url
     *
     * @param $consignmentId
     * @return $this
     */
    public function createRetourlinkRequest($consignmentId)
    {
        $data = array('id' => (int)$consignmentId);

        $requestString = $this->_createRequestString($data, 'parent_shipments');

        $this->_setRequestParameters($requestString, 'create_related_return_shipment_link', self::REQUEST_HEADER_RETURN);

        return $this;
    }

    /**
     * Shipment v2 endpoint active from x number of orders
     *
     * @param $numberOfOrders
     * @return bool
     */
    public function useShipmentV2($numberOfOrders)
    {
        return $numberOfOrders > self::SHIPMENT_V2_ACTIVE_FROM ? true : false;
    }

    /**
     * Checks if all the requirements are set to send a request to MyParcel
     *
     * @return bool
     */
    protected function _checkConfigForRequest()
    {
        if(empty($this->apiUsername) || empty($this->apiKey)){
            return false;
        }

        if(empty($this->requestType)){
            return false;
        }

        if(empty($this->requestString)){
            return false;
        }

        return true;
    }

    /**
     * Gets the shipping address and product code data for this shipment.
     *
     * @param TIG_MyParcel2014_Model_Shipment $myParcelShipment
     *
     * @return array
     *
     * @throws TIG_MyParcel2014_Exception
     */
    protected function _getConsignmentData(TIG_MyParcel2014_Model_Shipment $myParcelShipment)
    {
        // @var TIG_MyParcel2014_Helper_Data $helper
        $helper       = Mage::helper('tig_myparcel');
        $order        = $myParcelShipment->getOrder();
        $storeId      = $order->getStore()->getId();
        $checkoutData = json_decode($myParcelShipment->getOrder()->getMyparcelData(), true);
        $countryCode  = $myParcelShipment->getShippingAddress()->getCountry();
        $WeightData   = [];

        if ($storeId != $this->getStoreId()) {
            $this->apiUsername = $helper->getConfig('username', 'api', $storeId);
            $this->apiKey      = $helper->getConfig('key', 'api', $storeId, true);
        }

        $shippingAddress = $myParcelShipment->getShippingAddress();
        $streetData      = $helper->getStreetData($shippingAddress);
        $email           = $myParcelShipment->getOrder()->getCustomerEmail();

        $data = array(
            'recipient' => array(
                'cc'            => $shippingAddress->getCountry(),
                'person'        => trim($shippingAddress->getName()),
                'company'       => (string) trim($shippingAddress->getCompany()),
                'postal_code'   => trim($shippingAddress->getPostcode()),
                'street'        => trim($streetData['streetname']),
                'number'        => trim($streetData['housenumber']),
                'region'        => trim($shippingAddress->getRegion()),
                'number_suffix' => trim($streetData['housenumberExtension']),
                'city'          => trim($shippingAddress->getCity()),
                'email'         => $email,
            ),

            'options'    => $this->_getOptionsData($myParcelShipment, $checkoutData, $countryCode),
            'secondary_shipments' => $this->getSecondaryShipmentsData($myParcelShipment, $countryCode)
        );

        if ($countryCode != 'NL') {
            $phone = $order->getBillingAddress()->getTelephone();
            if ($phone) {
                $data['recipient']['phone'] = $phone;
            }

            $streetParts                 = $this->getInternationalStreetParts($streetData);
            $data['recipient']['street'] = $streetParts[0];
            if (isset($streetParts[1])) {
                $data['recipient']['street_additional_info'] = $streetParts[1];
            }
            unset($data['recipient']['number']);
            unset($data['recipient']['number_suffix']);
        }

        if ((int) $myParcelShipment['multi_collo_amount'] <= 1){
            unset($data['secondary_shipments']);
        }

        $totalWeight = 0;
        $items       = $myParcelShipment->getOrder()->getAllItems();
        $i           = 0;

        // add customs data for EUR3 and World shipments
        if ($helper->countryNeedsCustoms($shippingAddress->getCountry())) {

            $customsContentType = null;
            if ($myParcelShipment->getCustomsContentType()) {
                $customsContentType = explode(',', $myParcelShipment->getCustomsContentType());
            }

            if ($data['options']['package_type'] == 2) {
                throw new TIG_MyParcel2014_Exception(
                    $helper->__('International shipments can not be sent by') . ' ' . strtolower($helper->__('Letter box')),
                    'MYPA-0027'
                );
            }

            $data['customs_declaration']             = array();
            $data['customs_declaration']['items']    = array();
            $data['customs_declaration']['invoice']  = $order->getIncrementId();
            $customType                              = (int) $helper->getConfig('customs_type', 'shipment', $storeId);
            $data['customs_declaration']['contents'] = $customType == 0 ? 1 : $customType;

            foreach ($items as $item) {
                if ($item->getProductType() == 'simple') {

                    $WeightData = $this->getTotalWeight($totalWeight, $item, $myParcelShipment, true);

                    if (empty($customsContentType)) {
                        $customsContentTypeItem = $helper->getHsCode($item, $storeId);
                    } else {
                        $customsContentTypeItem = key_exists($i, $customsContentType) ? $customsContentType[$i] : $customsContentType[0];
                    }
                    if (! $customsContentTypeItem) {
                        throw new TIG_MyParcel2014_Exception(
                            $helper->__('No Customs Content HS Code found. Go to the MyParcel plugin settings to set this code.'),
                            'MYPA-0026'
                        );
                    }

                    $itemDescription = $item->getName();

                    if (strlen($itemDescription) > 50) {
                        $itemDescription = substr($itemDescription, 0, 50);
                    }

                    $calculateWeight = $this->getCalculatedWeightToGram($WeightData['weight'], $myParcelShipment);

                    $data['customs_declaration']['items'][] = array(
                        'description'    => $itemDescription,
                        'amount'         => $WeightData['qty'],
                        'weight'         => $calculateWeight,
                        'item_value'     => array('amount' => $WeightData['price'] * 100, 'currency' => 'EUR'),
                        'classification' => $customsContentTypeItem,
                        'country'        => Mage::getStoreConfig('general/country/default', $storeId),

                    );

                    if (++ $i >= 5) {
                        break; // max 5 entries
                    }
                }
            }
            $data['customs_declaration']['weight'] = (int)$WeightData['total_weight'];

        }

        if($data['options']['package_type'] == TIG_MyParcel2014_Model_Shipment::TYPE_DIGITAL_STAMP_NUMBER){
            foreach($items as $item) {
                if($item->getProductType() == 'simple') {
                    $WeightData = $this->getTotalWeight($totalWeight, $item);
                }
            }
            unset($data['options']['delivery_date']);
            unset($data['options']['weight']);

            // Throw Exception when the weight of the digital stamp is more than 2000 grams
            if ($WeightData['total_weight'] > $myParcelShipment::WEIGHT_DIGITAL_STAMP){
                throw new TIG_MyParcel2014_Exception(
                    $helper->__('The total weight of the order is more than 2000 grams and can not be sent with a digital stamp.'),
                    'MYPA-0028'
                );
            }
        }
        if ($WeightData['total_weight']){
            $data['physical_properties']['weight'] = (int)$WeightData['total_weight'];
        }

        /**
         * If the customer has chosen to pick up their order at a PakjeGemak location, add the PakjeGemak address.
         */
        $pgAddress      = $helper->getPgAddress($myParcelShipment);
        $shippingMethod = $order->getShippingMethod();

        if ($pgAddress && $helper->shippingMethodIsPakjegemak($shippingMethod)) {
            $pgStreetData      = $helper->getStreetData($pgAddress);
            $data['options']['signature'] = 1;
            $data['pickup'] = array(
                'postal_code'       => trim($pgAddress->getPostcode()),
                'street'            => trim($pgStreetData['streetname']),
                'city'              => trim($pgAddress->getCity()),
                'number'            => trim($pgStreetData['housenumber']),
                'location_name'     => trim($pgAddress->getCompany()),
            );

            if (key_exists('retail_network_id', $checkoutData)) {
                $data['pickup']['location_code'] = $checkoutData['location_code'];
                $data['pickup']['retail_network_id'] = $checkoutData['retail_network_id'];
            }
        }

        $data['carrier'] = 1;
        return $data;
    }

    /**
     * @param \TIG_MyParcel2014_Model_Shipment $myParcelShipment
     * @param $countryCode
     * @param null $data
     *
     * @return array|null
     */
    public function getSecondaryShipmentsData(TIG_MyParcel2014_Model_Shipment $myParcelShipment, $countryCode, $data = null)
    {

        $multicolloAmount = (int) $myParcelShipment['multi_collo_amount'];

        if ($countryCode != 'NL' &&
            $countryCode != 'BE' &&
            $myParcelShipment->getShipmentType() !== $myParcelShipment::TYPE_PACKAGE_NUMBER
        ) {
            return null;
        }

        $i = 1;
        $multicolloAmount--;
        while ($i <= $multicolloAmount) {
            $data[] = (object) [];
            $i ++;
        }

        return $data;
    }

     /**
     * @param int|float                        $totalWeight
     * @param mixed                            $item
     * @param \TIG_MyParcel2014_Model_Shipment $myParcelShipment
     * @param bool                             $isWoldShipment
     *
     * @return array
     */
    public function getTotalWeight($totalWeight, $item, $myParcelShipment, $isWoldShipment = false) {

        $parentId   = $item->getParentItemId();
        $weight     = floatval($item->getWeight());
        $price      = floatval($item->getPrice());
        $qty        = intval($item->getQtyOrdered());

        if ( ! empty($parentId)) {
            $parent = Mage::getModel('sales/order_item')->load($parentId);

            if (empty($weight)) {
                $weight = $parent->getWeight();
            }

            if (empty($price)) {
                $price = $parent->getPrice();
            }
        }

        $weight *= $qty;

        $totalWeight += $this->getCalculatedWeightToGram($weight, $myParcelShipment);

        $price *= $qty;
        return ['weight' => $weight, 'total_weight' => $totalWeight, 'qty' => $qty, 'price' => $price];
    }

    /**
     * @param int|float                        $weight
     * @param \TIG_MyParcel2014_Model_Shipment $myParcelShipment
     *
     * @return int
     */
    protected function getCalculatedWeightToGram($weight, $myParcelShipment)
    {
        /**
        *  @var TIG_MyParcel2014_Helper_Data $helper
        */
        $helper  = Mage::helper('tig_myparcel');
        $order   = $myParcelShipment->getOrder();
        $storeId = $order->getStore()->getId();

        $weightType = $helper->getConfig('weight_indication', 'general', $storeId);

        if ($weightType != 'gram') {
            return (int) ($weight * 1000);
        }

        return (int) $weight;
    }

    /**
     * Gets the product code parameters for this shipment.
     *
     * @param TIG_MyParcel2014_Model_Shipment $myParcelShipment
     * @param array                           $checkoutData
     * @param string                          $countryCode
     *
     * @return array
     * @throws \Exception
     */
    protected function _getOptionsData(TIG_MyParcel2014_Model_Shipment $myParcelShipment, $checkoutData, $countryCode)
    {

        /**
         * @var TIG_MyParcel2014_Helper_Data $helper
         */
        $helper            = Mage::helper('tig_myparcel');
        $addressValidation = new TIG_MyParcel2014_Helper_AddressValidation;
        $order             = $myParcelShipment->getOrder();

        // Add the shipment type parameter.
        switch ($myParcelShipment->getShipmentType()) {
            case $myParcelShipment::TYPE_LETTER_BOX:
                /* Use mailbox only if no option is selected */
                if ($helper->shippingMethodIsPakjegemak($order->getShippingMethod())) {
                    $packageType = $myParcelShipment::TYPE_PACKAGE_NUMBER;
                } else {
                    $packageType = $myParcelShipment::TYPE_MAILBOX_NUMBER;
                }
                break;
            case $myParcelShipment::TYPE_UNPAID:
                $packageType = $myParcelShipment::TYPE_LETTER_NUMBER;
                break;
            case $myParcelShipment::TYPE_DIGITAL_STAMP:
                $packageType = $myParcelShipment::TYPE_DIGITAL_STAMP_NUMBER;
                break;
            case $myParcelShipment::TYPE_NORMAL:
            default:
                $packageType = $myParcelShipment::TYPE_PACKAGE_NUMBER;
                break;
        }

        $data = array(
            'package_type'          => $packageType,
            'large_format'          => (int)$myParcelShipment->isXL(),
            'only_recipient'        => (int)$myParcelShipment->isHomeAddressOnly(),
            'signature'             => (int)$myParcelShipment->isSignatureOnReceipt(),
            'return'                => (int)$myParcelShipment->getReturnIfNoAnswer(),
            'label_description'     => $order->getIncrementId(),
            'age_check'             => (int)$addressValidation->hasAgeCheck($order->getStoreId()),
        );

        if ($checkoutData !== null) {

            $data = $helper->getDeliveryType($checkoutData, $data, $order);

            if (key_exists('date', $checkoutData) && $checkoutData['date'] !== null) {
                $checkoutDateTime = $checkoutData['date'] . ' 00:00:00';
                $currentDateTime = $currentDate = new dateTime();
                $currentDate = $currentDate->format('Y-m-d') . ' 00:00:00';
                if (date_parse($checkoutDateTime) > date_parse($currentDate)) {
                    $data['delivery_date'] = $checkoutDateTime;
                } else {
                    $currentDateTime->modify('+1 day');
                    $nextDeliveryDay = $this->getNextDeliveryDay($currentDateTime);
                    $data['delivery_date'] = $nextDeliveryDay->format('Y-m-d 00:00:00');
                }

                if ((int) $helper->getConfig('deliverydays_window', 'checkout') > 1) {
                    $dateTime = date_parse($checkoutData['date']);
                    $data['label_description'] = $data['label_description'] . ' (' . $dateTime['day'] . '-' . $dateTime['month'] . ')';
                }
            }

        }

        if ((int)$myParcelShipment->getInsured() === 1 && $data['package_type'] != 2) {
            $data['insurance']['amount'] = $this->_getInsuredAmount($myParcelShipment) * 100;
            $data['insurance']['currency'] = 'EUR';
        }

		if ($countryCode != 'NL' || $data['package_type'] == 2) {
			// strip all Dutch domestic options if shipment is not NL or package_type is mailbox
			unset($data['only_recipient']);
			unset($data['signature']);
			unset($data['return']);
			unset($data['delivery_date']);
			unset($data['age_check']);
		}

        return $data;
    }

    /**
     * @param dateTime $dateTime
     *
     * @return mixed
     */
    private function getNextDeliveryDay($dateTime)
    {
        $weekDay = $dateTime->format("W");
        if ($weekDay == 0 || $weekDay == 6) {
            $dateTime->modify('+1 day');
            $dateTime = $this->getNextDeliveryDay($dateTime);
        }

        return $dateTime;
    }

    /**
     * Get the insured amount for this shipment.
     *
     * @param TIG_MyParcel2014_Model_Shipment $myParcelShipment
     *
     * @return int
     */
    protected function _getInsuredAmount(TIG_MyParcel2014_Model_Shipment $myParcelShipment)
    {
        if ($myParcelShipment->getInsured()) {
            return (int) $myParcelShipment->getInsuredAmount();
        }

        return 0;
    }

    /**
     * Creates a url-encoded request string.
     *
     * @param array $data
     * @param string $dataType
     *
     * @return string
     */
    protected function _createRequestString(array $data, $dataType = 'shipments')
    {
        $requestData['data'][$dataType][] = $data;

        return json_encode($requestData);
    }

    /**
     * Generating positions for A4 paper
     *
     * @param int $start
     * @return string
     */
    protected function _getPositions($start)
    {
        $aPositions = array();
        switch ($start){
            case 1:
                $aPositions[] = 1;
            case 2:
                $aPositions[] = 2;
            case 3:
                $aPositions[] = 3;
            case 4:
                $aPositions[] = 4;
                break;
        }

        return implode(';',$aPositions);
    }

	/**
	 * Wraps a street to max street lenth
	 *
	 * @param $streetData
	 *
	 * @return array
	 */
	private function getInternationalStreetParts ($streetData)
	{
		unset($streetData['fullStreet']);

		// replace double whitespaces
		$street = trim( str_replace( '  ', ' ', implode( ' ', $streetData ) ) );

		// split street in 2 parts
		return explode("\n", wordwrap($street, self::MAX_STREET_LENGTH));
	}
}
