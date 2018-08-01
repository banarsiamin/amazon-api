<?php
/**********************************************************
* Update inventory stock through amazon mws api
*
***********************************************************/

$sku1        = '18031NA';
$quantity1   = '9';
$leadTimeToShip1 = '7';

//amazon mws credentials
$amazonSellerId         = '*******';
$amazonMWSAuthToken     = '*******';
$amazonAWSAccessKeyId   = '*******';
$amazonSecretKey        = '*******';
$amazonMarketPlaceId    = '*******';
$param = array();
$param['AWSAccessKeyId']     = $amazonAWSAccessKeyId;
$param['Action']             = 'SubmitFeed'; 
$param['Merchant']           = $amazonSellerId;
$param['MWSAuthToken']       = $amazonMWSAuthToken; 
$param['FeedType']      	 = '_POST_INVENTORY_AVAILABILITY_DATA_';
$param['SignatureMethod']    = 'HmacSHA256';  
$param['SignatureVersion']   = '2'; 
$param['Timestamp']          = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
$param['Version']            = '2009-01-01'; 
$param['MarketplaceIdList.Id.1'] = $amazonMarketPlaceId;
$param['PurgeAndReplace']    = 'false';
$secret = $amazonSecretKey;
$url = array();
foreach ($param as $key => $val) {
    $key = str_replace("%7E", "~", rawurlencode($key));
    $val = str_replace("%7E", "~", rawurlencode($val));
    $url[] = "{$key}={$val}";
}


$amazon_feed = '<?xml version="1.0" encoding="utf-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
    <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>'.$amazonSellerId.'</MerchantIdentifier>
    </Header>
    <MessageType>Inventory</MessageType>
    <Message>
    <MessageID>1</MessageID>
    <OperationType>Update</OperationType>
    <Inventory>
    <SKU>'.$sku1.'</SKU>
    <Quantity>'.$quantity1.'</Quantity>
    </Inventory>
    </Message>
    </AmazonEnvelope>';// <FulfillmentLatency>'.$leadTimeToShip1.'</FulfillmentLatency>

sort($url);

$arr   = implode('&', $url);

$sign  = 'POST' . "\n";
$sign .= 'mws.amazonservices.com' . "\n";
$sign .= '/Feeds/'.$param['Version'].'' . "\n";
$sign .= $arr;

$signature      = hash_hmac("sha256", $sign, $secret, true);
$httpHeader     =   array();
$httpHeader[]   =   'Transfer-Encoding: chunked';
$httpHeader[]   =   'Content-Type: application/xml';
$httpHeader[]   =   'Content-MD5: ' . base64_encode(md5($amazon_feed, true));
//$httpHeader[]   =   'x-amazon-user-agent: MyScriptName/1.0';
$httpHeader[]   =   'Expect:';
$httpHeader[]   =   'Accept:';              
$signature      = urlencode(base64_encode($signature));
$link  = "https://mws.amazonservices.com/Feeds/".$param['Version']."?";
$link .= $arr . "&Signature=" . $signature;

$ch = curl_init($link);
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $amazon_feed); 
$response = curl_exec($ch);
$info = curl_getinfo($ch);
$errors=curl_error($ch);
curl_close($ch);

echo '<pre>';
print_r($response); //xml response


?>