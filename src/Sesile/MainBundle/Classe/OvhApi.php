<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 03/02/2015
 * Time: 14:59
 */

namespace Sesile\MainBundle\Classe;

define('OVH_API_EU', 'https://eu.api.ovh.com/1.0');
define('OVH_API_CA', 'https://ca.api.ovh.com/1.0');

class OvhApi {

    var $AK;
    var $AS;
    var $CK;
    var $timeDrift = 0;
    function __construct($_root, $_ak, $_as, $_ck) {
        // INIT vars
        $this->AK = $_ak;
        $this->AS = $_as;
        $this->CK = $_ck;
        $this->ROOT = $_root;

        // Compute time drift
        $serverTimeRequest = file_get_contents($this->ROOT . '/auth/time');
        if($serverTimeRequest !== FALSE)
        {
            $this->timeDrift = time() - (int)$serverTimeRequest;
        }
    }
    function call($method, $url, $body = NULL)
    {
        $url = $this->ROOT . $url;
        if($body)
        {
            $body = json_encode($body);
        }
        else
        {
            $body = "";
        }

        // Compute signature
        $time = time() - $this->timeDrift;
        $toSign = $this->AS.'+'.$this->CK.'+'.$method.'+'.$url.'+'.$body.'+'.$time;
        $signature = '$1$' . sha1($toSign);

        // Call
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'X-Ovh-Application:' . $this->AK,
            'X-Ovh-Consumer:' . $this->CK,
            'X-Ovh-Signature:' . $signature,
            'X-Ovh-Timestamp:' . $time,
        ));
        if($body)
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($curl);
        if($result === FALSE)
        {
            echo curl_error($curl);
            return NULL;
        }

        return json_decode($result);
    }
    function get($url)
    {
        return $this->call("GET", $url);
    }
    function put($url, $body)
    {
        return $this->call("PUT", $url, $body);
    }
    function post($url, $body)
    {
        return $this->call("POST", $url, $body);
    }
    function delete($url)
    {
        return $this->call("DELETE", $url);
    }
}