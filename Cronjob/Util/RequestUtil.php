<?php
/*
 *  ================================================================================
 *  File:  requestUtil.php
 *  @version 1.0.0
 *  @category
 *  @link
 *  @package
 *
 */

namespace Cronjob\Util;

class RequestUtil {

    function __construct() {
        
    }
    
    public static function getContents($psm_url, $pao_headers)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $psm_url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_PROXY, 'cis-cinci-pitc-ssow.proxy.corporate.ge.com');
        curl_setopt($ch, CURLOPT_PROXYPORT, 80);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if(!empty($pao_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $pao_headers);
        }
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200){
            throw new \Exception("HTTP ERROR: ". $info['http_code']);
        }
        curl_close($ch);
        return $data;
    }

}