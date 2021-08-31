<?php

namespace Teachers\Bundle\InvoiceBundle\Helper;

use DOMDocument;
use Exception;

class PaymentGateway
{
    const GATEWAY_URL = 'https://secure.networkmerchants.com/api/v2/three-step';
    const API_KEY = 'n3RApVU527kXv4PKnR4qV4dSqHS5SwU3';

    /**
     * @throws Exception
     */
    public function sale($amount, $redirectUrl)
    {
        $xmlRequest = new DOMDocument('1.0', 'UTF-8');
        $xmlRequest->formatOutput = true;
        $xmlSale = $xmlRequest->createElement('sale');
        $this->appendXmlNode($xmlRequest, $xmlSale, 'api-key', self::API_KEY);
        $this->appendXmlNode($xmlRequest, $xmlSale, 'redirect-url', $redirectUrl);
        $this->appendXmlNode($xmlRequest, $xmlSale, 'amount', $amount);
        $this->appendXmlNode($xmlRequest, $xmlSale, 'ip-address', $_SERVER["REMOTE_ADDR"]);
        $this->appendXmlNode($xmlRequest, $xmlSale, 'currency', 'USD');

        $xmlRequest->appendChild($xmlSale);

        return $this->sendXMLviaCurl($xmlRequest);
    }

    /**
     * @throws Exception
     */
    public function completeAction($token)
    {
        $xmlRequest = new DOMDocument('1.0', 'UTF-8');
        $xmlRequest->formatOutput = true;
        $xmlCompleteTransaction = $xmlRequest->createElement('complete-action');
        $this->appendXmlNode($xmlRequest, $xmlCompleteTransaction, 'api-key', self::API_KEY);
        $this->appendXmlNode($xmlRequest, $xmlCompleteTransaction, 'token-id', $token);
        $xmlRequest->appendChild($xmlCompleteTransaction);

        return $this->sendXMLviaCurl($xmlRequest);
    }

    /**
     * @throws Exception
     */
    public function refund($transaction, $amount)
    {
        $xmlRequest = new DOMDocument('1.0', 'UTF-8');
        $xmlRequest->formatOutput = true;
        $xmlCompleteTransaction = $xmlRequest->createElement('refund');
        $this->appendXmlNode($xmlRequest, $xmlCompleteTransaction, 'api-key', self::API_KEY);
        $this->appendXmlNode($xmlRequest, $xmlCompleteTransaction, 'transaction-id', $transaction);
        $this->appendXmlNode($xmlRequest, $xmlCompleteTransaction, 'amount', $amount);
        $xmlRequest->appendChild($xmlCompleteTransaction);

        return $this->sendXMLviaCurl($xmlRequest);
    }

    private function appendXmlNode($domDocument, $parentNode, $name, $value)
    {
        $childNode = $domDocument->createElement($name);
        $childNodeValue = $domDocument->createTextNode($value);
        $childNode->appendChild($childNodeValue);
        $parentNode->appendChild($childNode);
    }

    /**
     * @throws Exception
     */
    private function sendXMLviaCurl($xmlRequest)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::GATEWAY_URL);
        $headers = [];
        $headers[] = "Content-type: text/xml";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $xmlString = $xmlRequest->saveXML();
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        // This should be unset in production use. With it on, it forces the ssl cert to be valid
        // before sending info.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (!($data = curl_exec($ch))) {
            print  "curl error =>" . curl_error($ch) . "\n";
            throw new Exception(" CURL ERROR :" . curl_error($ch));
        }
        curl_close($ch);
        return $data;
    }
}
