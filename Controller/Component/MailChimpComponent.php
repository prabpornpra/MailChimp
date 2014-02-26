<?php
/**
 * Mailchimp Cakephp Component
 * API Documentation: http://www.mailchimp.com/api/
 *
 * portions of this code were based on or directly lifted from glamorous'
 * Mailchimp-PHP-API on github his is much more feature rich at the moment
 * mine was written specifically for cakephp
 *
 * https://github.com/glamorous/Mailchimp-PHP-API/blob/master/mailchimp.php
 * https://github.com/glamorous
 *
 * PHP Version 5
 *
 * @category Component
 * @package  Web
 * @author   Orabpornpra <orabpornpra@blendtec.com>
 * @date     02.18.2014
 * @license  The MIT License (MIT)
 * @link     https://github.com/ORabpornpra/MailChimp
 */
App::uses('Component', 'Controller');
App::uses('HttpSocket', 'Network/Http');
/**
 * Class MailChimpComponent
 *
 * @category Component
 * @package  Web
 * @author   Orabpornpra <orabpornpra@blendtec.com>
 * @license  The MIT License (MIT)
 * @link     https://github.com/ORabpornpra/MailChimp
 * @throws  Exception
 */
class MailChimpComponent extends Component {
	const JSON = 'json';
	const XML = 'xml';
	const PHP = 'php';
	const POST_TIMEOUT = 30;

	private $__apiUrl;

	private $__formats = array (
		MailChimpComponent::JSON,
		MailChimpComponent::XML,
		MailChimpComponent::PHP,
	);

	private $__apiKey;

	private $__format;

	private $__defaultFormat;

	private $__dc;

/**
 * Subscribe an email into list
 *
 * @param string $listId list id
 * @param string $params data for subscribe
 *
 * @return Json response in default
 *
 * @throws Exception
 */
	public function listSubscribe($listId, $params) {
		$params['id'] = $listId;
		$selectionMethod = Configure::read('Chimp.lists_subscribe');

		return $this->__makeCall($params, $selectionMethod);
	}

/**
 * Set api key for sending a request
 * Set dc, base on api key
 *
 * @param $apiKey
 *
 * @throws Exception
 */
	private function __setApikey($apiKey) {
		$this->__apiKey = (string)$apiKey;
		$this->__dc = substr($apiKey, -3);
	}

/**
 * Set the response format, option are JSON, PHP, XML, default is set to JSON
 *
 * @param $format
 *
 * @throws Exception
 */
	private function __setFormat($format) {
		if (in_array($format, $this->__formats)) {
			$this->__format = $format;
		} else {
			$this->__format = $this->__defaultFormat;
		}
	}

/**
 * Make a call to mailChimp api
 *
 * @param $params data for POST request
 * @param $sectionsMethod section and method we want to call in Api
 *
 * @return response in JSON format by default
 * @throws Exception
 */
	private function __makeCall($params, $sectionsMethod) {
		$HttpSocket = new HttpSocket(array('timeout' => MailChimpComponent::POST_TIMEOUT));
		$params['apikey'] = $this->__apiKey;

		// check if an API-key is provided
		if (!isset($params['apikey'])) {
			throw new Exception(__('API-key must be set'));
		}

		// check if the section and method is provided
		if ($sectionsMethod == null || empty($sectionsMethod)) {
			throw new Exception(
				__("Without a method this class can't call the API")
			);
		}

		// check if a format is provided
		if ($this->__format == null || empty($this->__format)) {
			$this->__format = $this->__defaultFormat;
		}

		$url = 'https://' . $this->__dc . '.' . $this->__apiUrl . $sectionsMethod . '.' . $this->__format . '/?' . http_build_query($params, null, '&');

		$result = $HttpSocket->post($url);
		return $result;
	}

/**
 * Get an information from specific email in specific list
 *
 * @param $listId list id
 * @param $email email that we want to get an info
 *
 * @return response in JSON format by default
 * @throws Exception
 */
	public function listMemberInfo($listId, $email) {
		$params = array(
			'id' => $listId,
			'emails' => array(array('email' => $email))
		);
		$sectionsMethod = Configure::read('Chimp.lists_member_info');

		return $this->__makeCall($params, $sectionsMethod);
	}

/**
 * get the email subscription status in specific list
 *
 * @param string $listId list id
 * @param string $email client email
 *
 * @return string status that retrieve from mailChimp
 * @throws Exception
 */
	public function getEmailStatus($listId, $email) {
		$response = $this->listMemberInfo($listId, $email);
		$resultArray = $this->getData($response);
		$emailStatus = 'Not subscribe';
		if ($response->isOk()) {
			if ($resultArray['success_count'] != 0) {
				$emailStatus = $this->getDataSpecificKey($resultArray['data']['0'], 'status');
			}
		} else {
			$errorMessage = $resultArray['name'] . ': ' . $resultArray['error'];
			throw new Exception(__($errorMessage));
		}

		return $emailStatus;
	}

/**
 * Decode JSON response and add it into an array
 *
 * @param $response JSON response
 *
 * @return array
 */
	public function getData($response) {
		$resultBody = $response->body();
		$resultBodyDecode = json_decode($resultBody);
		$resultArray = array();
		foreach ($resultBodyDecode as $key => $value) {
			$resultArray[$key] = $value;
		}
		return $resultArray;
	}

/**
 * Get a value from specific key
 *
 * @param array $data
 * @param string $specificKey
 *
 * @return string
 */
	public function getDataSpecificKey($data, $specificKey) {
		$valueSpecificKey = '';
		foreach ($data as $keyId => $valueId) {
			if ($keyId == $specificKey) {
				$valueSpecificKey = $valueId;
				break;
			}
		}
		return $valueSpecificKey;
	}

/**
 * initialize
 *
 * @param Controller $controller controller
 *
 * @return void
 */
	public function initialize(Controller $controller) {
		Configure::load('mail_chimp');
		$this->__apiUrl = Configure::read('Chimp.url') . Configure::read('Chimp.version');
		$this->__defaultFormat = MailChimpComponent::JSON;
		$this->__setApikey(Configure::read('Chimp.key'));
		$this->__setFormat(MailChimpComponent::JSON);
	}
}
