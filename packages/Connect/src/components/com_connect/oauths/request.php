<?php

require_once 'core.php';

/**
 * Service Request.
 *
 * @category   Anahita
 *
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 *
 * @link       http://www.GetAnahita.com
 */
class ComConnectOauthRequest extends AnObject
{
    /**
     * Internal Oauth Request.
     *
     * @var OAuthRequest
     */
    protected $_internal_request;

    /**
     * CURL Request OPtions.
     *
     * @var AnConfig
     */
    protected $_options;

    /**
     * The data to send.
     *
     * @var mixed
     */
    protected $_data;

    /**
     * Constructor.
     *
     * @param 	object 	An optional AnConfig object with configuration options
     */
    public function __construct($config = array())
    {
        if (is_array($config)) {
            $config = new AnConfig($config);
        }

        parent::__construct($config);

        $params = new AnConfig();

        $this->_data = AnConfig::unbox($config->data);

        if (is_array($this->_data)) {
            $params->append($this->_data);
        }

        $params->append(array(
            'oauth_version' => $config->version,
        ));

        $this->_internal_request = OAuthRequest::from_consumer_and_token($config->consumer, $config->token, $config->method, $config->url, AnConfig::unbox($params));

        if (!empty($config->signature)) {
            $this->_internal_request->sign_request($config->signature, $config->consumer, $config->token);
        }

        $this->_options = $config->options;
    }

    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	object 	An optional AnConfig object with configuration options.
     */
    protected function _initialize(AnConfig $config)
    {
        $config->append(array(
            'signature' => new OAuthSignatureMethod_HMAC_SHA1(),
            'method' => AnHttpRequest::GET,
            'data' => array(),
            'options' => array(
                'timeout' => 30,
                'connection_timeout' => 30,
                'ssl' => false,
                'useragent' => 'com_connect',
            ),
        ));

        parent::_initialize($config);
    }

    /**
     * Return the request URL.
     *
     * @return string
     */
    public function getURL()
    {
        $url = $this->_internal_request->get_normalized_http_url();

        if ($this->getMethod() == AnHttpRequest::GET) {
            $url = $this->_internal_request->to_url();
        }

        return $url;
    }

    /**
     * Return the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($this->_internal_request->get_normalized_http_method());
    }

    /**
     * Return the request data.
     *
     * @return string
     */
    public function getData()
    {
        if (is_string($this->_data)) {
            return $this->_data;
        } else {
            return $this->_internal_request->to_postdata();
        }
    }

    /**
     * Sends a request and returns the response.
     *
     * @return string
     */
    public function send()
    {
        $url = $this->getURL();
        $data = $this->getData();
        $method = $this->getMethod();

        $ch = curl_init();
        $options = $this->_options;
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => $options->useragent,
            CURLOPT_CONNECTTIMEOUT => $options->connection_timeout,
            CURLOPT_TIMEOUT => $options->timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => $options->ssl,
            CURLOPT_HEADER => false,
        ));

        $headers = array();
        //if data is string then
        //set the authorization header
        //A Hack to make the linked-in work wihtout affecting
        //other services
        if (is_string($this->_data)) {
            $headers[] = $this->_internal_request->to_header();
            $headers[] = 'Content-Type: text/plain';
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        switch ($method) {
            case AnHttpRequest::POST  :
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case AnHttpRequest::PUT    :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case AnHttpRequest::DELETE:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);

        return new ComConnectOauthResponse($response, curl_getinfo($ch));
    }
}
