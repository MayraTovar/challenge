<?php
/*
 *  ================================================================================
 *  @copyright(C) 2012 General Electric. ALL RIGHTS RESERVED.
 * 
 *  This file contains proprietary and GE CONFIDENTIAL Information.
 *  Use, disclosure or reproduction is prohibited.
 * 
 *  File:  MultipleProxyClient.php
 *  Created On: 14-Nov-2012 17:03:09
 *  @author: mayra.tovar <mayra.tovar@ge.com>
 *  @version 1.0.0
 *  @category 
 *  @link     
 *  @package 
 * 
 */

namespace Cronjob\Core\Http;

use Zend\Http\Client;
use Zend\Http\Response;
use \Exception;
use Zend\Http\Client\Adapter\Proxy;

class MultipleProxyClient extends Client
{

    private $proxyPort = 80; # The proxy to use
    private $timeout = 600; # The timeout amount of seconds before it drops out (10 min)
    private $multipleProxySettings = array();

    /**
     * HTTP request methods
     */

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const HEAD = 'HEAD';
    const DELETE = 'DELETE';
    const TRACE = 'TRACE';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
    const MERGE = 'MERGE';

    public function __construct($uri = null, $config = null, $timeout = 600)
    {
        parent::__construct($uri, $config);
        $this->timeout = $timeout;
        $pao_connSettings = array();
        $pao_con_template = array(
            #  'proxy_port' => $this->proxyPort,
            'timeout' => $this->timeout,
        );

        $proxies = \Application\Util\Proxy::getAll();
        foreach ($proxies as $proxy) {
            $pao_con_template['proxy_host'] = $proxy;
            $pao_connSettings[] = $pao_con_template;
        }
        $this->multipleProxySettings = $pao_connSettings;
    }

    /**
     *
     * @return Response
     */
    public function doRequestMultipleProxies()
    {
        $adapter = new Proxy();
        for ($i = 0; $i < count($this->multipleProxySettings); $i++) {
            $adapter->setOptions($this->multipleProxySettings[$i]);
            try {
                $this->setAdapter($adapter);
                $zendHttpResponse = $this->send();
                if ($zendHttpResponse->getStatusCode() !== 200) {//Invalid proxy o maybe invalid URL try another proxy.
                    throw new Exception("Error code:" . $zendHttpResponse->getStatusCode());
                }
                return $zendHttpResponse;
            } catch (Exception $e) {
                echo ($e->getMessage());
            }
        }
        throw new Exception("Unable to connect. No proxy can connect to the provided URL:" . $this->getUri(true));
    }
}
