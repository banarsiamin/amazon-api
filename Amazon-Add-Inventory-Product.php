<?php
/**********************************************************
* Update inventory stock through amazon mws api
* @banarsiamin  => banarsiamin@gmail.com
***********************************************************/
function RandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(2, $charactersLength - 1)];
    }
    return strtoupper($randomString);
}
echo $SKU =RandomString(6);
echo"<BR>";
//amazon mws credentials
$amazonSellerId         = '******';
$amazonMWSAuthToken     = '******';
$amazonAWSAccessKeyId   = '******';
$amazonSecretKey        = '******';
$amazonMarketPlaceId    = '******';
$param = array();
$param['AWSAccessKeyId']     = $amazonAWSAccessKeyId;
$param['Action']             = 'SubmitFeed'; 
$param['Merchant']           = $amazonSellerId;
$param['MWSAuthToken']       = $amazonMWSAuthToken; 
$param['FeedType']      	 = '_POST_PRODUCT_DATA_';
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
echo $dname = date('YmdHis');
echo"<BR>";
 $amazon_feed = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
<Header> 
<DocumentVersion>1.01</DocumentVersion>
<MerchantIdentifier>$amazonSellerId</MerchantIdentifier> 
</Header> 
<MessageType>Product</MessageType>
<PurgeAndReplace>false</PurgeAndReplace>
<Message> 
  <MessageID>1</MessageID> 
  <OperationType>Update</OperationType> 
  <Product> 
    <SKU>IBR-$SKU</SKU>
      <ProductTaxCode>A_GEN_TAX</ProductTaxCode>
      <DescriptionData> 
        <Title>IBRDEMO AMIN KHAN $dname</Title>
        <Brand>Bronzioni</Brand>
        <Description>IBRDEMO KHAN $dname</Description>
        <BulletPoint>10"X14". 12 LBS...Real Bronze Statues Sculptures Collectible Gift Office and Home Decor1</BulletPoint>
        <BulletPoint>Enjoy the workmanship, details and the elegance for generations.2</BulletPoint> 
        <BulletPoint>Real Hot Cast Bronze Sculpture.3</BulletPoint> 
        <BulletPoint> This happy elephant lifts her trunk and swings her tail letting you know she is in a blissful mood. She lifts her trunk and exposes the underside which is adorned in a gorgeous shiny gold patina. She is an innocent little thing with small beady eyes and4</BulletPoint> 
        <BulletPoint>Customer service phone numbers: U.S.A. (+1.516.880.9959) U.K. (+44.20.37697845) France: (+33.9.75121702) Australia (+61.2.90984493)5</BulletPoint>  
        <ShippingWeight unitOfMeasure="LB">12</ShippingWeight>
        <MSRP currency="USD">123.90</MSRP> 
        <Manufacturer>Bronze</Manufacturer>
        <MfrPartNumber>IBR-$SKU-123</MfrPartNumber>
        <SearchTerms>Animals Statues</SearchTerms> 
        <SearchTerms>Animals sculptures</SearchTerms> 
        <SearchTerms>Bronze Figurines</SearchTerms> 
        <SearchTerms>Bronze Sculptures</SearchTerms> 
        <SearchTerms>bronze statues</SearchTerms> 
        <UsedFor>Gift</UsedFor> 
        <UsedFor>Decorative</UsedFor> 
        <UsedFor>Collectables</UsedFor> 
        <UsedFor>Anniversary</UsedFor> 
        <UsedFor>Birthday</UsedFor> 
        <ItemType>statues</ItemType>
        <OtherItemAttributes>Antique Style</OtherItemAttributes>
        <OtherItemAttributes>Art Deco</OtherItemAttributes>
        <OtherItemAttributes>Decorative</OtherItemAttributes>
        <OtherItemAttributes>Bronze</OtherItemAttributes>
        <OtherItemAttributes>Contemporary</OtherItemAttributes>
      </DescriptionData> 
      <ProductData> 
        <Home> 
          <ProductType> 
            <FurnitureAndDecor> 
              <Material>Bronze</Material> 
            </FurnitureAndDecor> 
          </ProductType>  
      </Home> 
    </ProductData> 
  </Product> 
</Message> 
</AmazonEnvelope>
EOD;
?>
    <?php
$amazon_feed=trim($amazon_feed);
//echo "$amazon_feed";//die;
sort($url);

$arr   = implode('&', $url);

$sign  = 'POST' . "\n";
$sign .= 'mws.amazonservices.com' . "\n";
$sign .= '/Feeds/'.$param['Version'].''. "\n";
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
print_r(json_encode($response)); //xml response


?>
