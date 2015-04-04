<?php

function curl_redirect_exec($ch, $curlopt_header = false) {
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $data = curl_exec($ch);
  $info =    curl_getinfo($ch);
  $http_code = $info['http_code'];
  if ($http_code == 301 || $http_code == 302 || $http_code == 303) {
    list($header) = explode("\r\n\r\n", $data, 2);
    $matches = array();
    preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
    $url = trim(array_pop($matches));
    $url_parsed = parse_url($url);
    if (isset($url_parsed['host'])) {
      curl_setopt($ch, CURLOPT_URL, $url);
      return curl_redirect_exec($ch);
    }
  }

  elseif($http_code == 200){
    $matches = array();
    preg_match('/(<meta http-equiv=)(.*?)(refresh)(.*?)(url=)(.*?)[\'|"]\s*>/', strtolower($data), $matches);
    $url = trim(array_pop($matches));
    $url_parsed = parse_url($url);
    if (isset($url_parsed['host'])) {
      curl_setopt($ch, CURLOPT_URL, $url);
      return curl_redirect_exec($ch);
    }
  }

  return     $info['url'];
} 

class GoogleVoice
{
  const loginURL = "https://www.google.com/accounts/ClientLogin";
  const genURL   = "https://www.google.com/voice/";
  const sendSMSURL = "https://www.google.com/voice/sms/send/";

	// Our credentials
	private $_login;
	private $_pass;
	private $_rnr_se; // some crazy google thing that we need later
        private $_auth;

	// Our private curl handle
	private $_ch;

	// The location of our cookies
	private $_cookieFile;

	// Are we logged in already?
	private $_loggedIn = FALSE;

	public function __construct($login, $pass)
	{
		$this->_login = $login;
		$this->_pass = $pass;

		$config = cmsms()->getConfig();
		$this->_cookieFile = $config['root_url'].'/tmp/cache/gvCookies.txt';

		$this->_ch = curl_init();
		curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $this->_cookieFile);
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($this->_ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");
	}
	
	private function _logIn()
	{
		global $conf;

		if( $this->_loggedIn )
		  return TRUE;

		//
		// do the login and fetch AUTH
		//
		//curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($this->_ch, CURLOPT_URL, self::loginURL);
		curl_setopt($this->_ch, CURLOPT_POST, TRUE);
		$data = array(
			      'accountType' => 'GOOGLE',
			      'Email' => $this->_login,
			      'Passwd' => $this->_pass,
			      'service' => 'grandcentral',
			      'source' => 'SMSG'
			);
		debug_display($data);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data );	
		$html = curl_exec($this->_ch);
		$rescode = curl_getinfo($this->_ch,CURLINFO_HTTP_CODE);
		if( $rescode > 399 )
		  {
		    throw new Exception('Attempt to get Login failed with code '.$rescode);
		  }

		if( $html == '' )
		  {
		    throw new Exception('Attmpt to login - result empty');
		  }

		if( preg_match('/Auth=([_a-zA-Z0-9]+)/', $html, $matches) )
		  {
		    $this->_auth = $matches[1];
		  }
		else
		  {
		    debug_display($html); die();
		    throw new Exception('Could not get auth token');
		  }

		//
		// get the rnr_see value
		//
		curl_setopt($this->_ch, CURLOPT_URL, self::genURL.'?auth='.$this->_auth);
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
		$html = curl_exec($this->_ch);
		debug_display(curl_getinfo($this->_ch));
		$rescode = curl_getinfo($this->_ch,CURLINFO_HTTP_CODE);
		if( $rescode > 399 )
		  {
		    throw new Exception('Attempt to get Login failed with code '.$rescode);
		  }

		if( preg_match('/name="_rnr_se".*?value="(.*?)"/', $html, $match) )
		{
			$this->_rnr_se = $match[1];
		}
		else
		{
		  debug_display($html); die();
			throw new Exception("Could not log in to Google Voice with username: " . $this->_login);
		}

	}

	/**
	 * Place a call to $number connecting first to $fromNumber
	 * @param $number The 10-digit phone number to call (formatted with parens and hyphens or none)
	 * @param $fromNumber The 10-digit number on your account to connect the call (no hyphens or spaces)
	 * @param $phoneType (mobile, work, home, gizmo) The type of phone the $fromNumber is. The call will not be connected without this value. 
	 */
	public function callNumber($number, $fromNumber, $phoneType='mobile')
	{
		$types = array(
			'mobile' => 2,
			'work' => 3,
			'home' => 1,
			'gizmo' => 7
		);
	
		if(!array_key_exists($phoneType, $types))
			throw new Exception('Phone type must be mobile, work, home or gizmo');
		
		$this->_logIn();
		
		curl_setopt($this->_ch, CURLOPT_URL, 'https://www.google.com/voice/call/connect/');
		curl_setopt($this->_ch, CURLOPT_POST, TRUE);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, array(
			'_rnr_se'=>$this->_rnr_se,
			'auth'=>$this->_auth,
			'forwardingNumber'=>'+1' . $fromNumber,
			'outgoingNumber'=>$number,
			'phoneType'=>$types[$phoneType],
			'remember'=>0,
			'subscriberNumber'=>'undefined'
			));
		curl_exec($this->_ch);
	}

	public function sendSMS($number, $message)
	{
		$this->_logIn();

		curl_setopt($this->_ch, CURLOPT_URL, 'https://www.google.com/voice/sms/send/');
		curl_setopt($this->_ch, CURLOPT_POST, TRUE);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, array(
			'_rnr_se'=>$this->_rnr_se,
			'auth'=>$this->_auth,
			'phoneNumber'=>'+1' . $number,
			'text'=>$message,
			'id'=>''
			));
		$res = curl_exec($this->_ch);
		$rescode = curl_getinfo($this->_ch,CURLINFO_HTTP_CODE);
		if( $rescode != 200 )
		  {
		    throw new Exception('Attempt to get Login failed with code '.$rescode);
		  }
	}
	
	public function getNewSMS()
	{
		$this->_logIn();
		curl_setopt($this->_ch, CURLOPT_URL, 'https://www.google.com/voice/inbox/recent/sms/');
		curl_setopt($this->_ch, CURLOPT_POST, FALSE);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);
		$xml = curl_exec($this->_ch);

		$dom = new DOMDocument();

		// load the "wrapper" xml (contains two elements, json and html)
		$dom->loadXML($xml);
		$json = $dom->documentElement->getElementsByTagName("json")->item(0)->nodeValue;
		$json = json_decode($json);

		// now make a dom parser which can parse the contents of the HTML tag
		$html = $dom->documentElement->getElementsByTagName("html")->item(0)->nodeValue;
		// replace all "&" with "&amp;" so it can be parsed
		$html = str_replace("&", "&amp;", $html);
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		
		$results = array();
		
		foreach( $json->messages as $mid=>$convo )
		{
			$elements = $xpath->query("//div[@id='$mid']//div[@class='gc-message-sms-row']");
			if(!is_null($elements))
			{
				if( in_array('unread', $convo->labels) )
				{
					foreach($elements as $i=>$element)
					{
						$XMsgFrom = $xpath->query("span[@class='gc-message-sms-from']", $element);
						$msgFrom = '';
						foreach($XMsgFrom as $m)
							$msgFrom = trim($m->nodeValue);

						if( $msgFrom != "Me:" )
						{
							$XMsgText = $xpath->query("span[@class='gc-message-sms-text']", $element);
							$msgText = '';
							foreach($XMsgText as $m)
								$msgText = trim($m->nodeValue);
	
							$XMsgTime = $xpath->query("span[@class='gc-message-sms-time']", $element);
							$msgTime = '';
							foreach($XMsgTime as $m)
								$msgTime = trim($m->nodeValue);
	
							$results[] = array('msgID'=>$mid, 'phoneNumber'=>$convo->phoneNumber, 'message'=>$msgText, 'date'=>date('Y-m-d H:i:s', strtotime(date('m/d/Y ',intval($convo->startTime/1000)).$msgTime)));
						}
					}
				}
				else
				{
					//echo "This message is not unread\n";	
				}
			}
			else
			{
				//echo "gc-message-sms-row query failed\n";
			}
		}
		
		return $results;
	}
	
	public function markSMSRead($msgID)
	{
		$this->_logIn();

		curl_setopt($this->_ch, CURLOPT_URL, 'https://www.google.com/voice/inbox/mark/');
		curl_setopt($this->_ch, CURLOPT_POST, TRUE);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, array(
			'_rnr_se'=>$this->_rnr_se,
			'messages'=>$msgID,
			'read'=>1
			));
		curl_exec($this->_ch);
	}

	public function getNewVoicemail()
	{
		$this->_logIn();
		curl_setopt($this->_ch, CURLOPT_URL, 'https://www.google.com/voice/inbox/recent/voicemail/');
		curl_setopt($this->_ch, CURLOPT_POST, FALSE);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);
		$xml = curl_exec($this->_ch);

		$dom = new DOMDocument();

		// load the "wrapper" xml (contains two elements, json and html)
		$dom->loadXML($xml);
		$json = $dom->documentElement->getElementsByTagName("json")->item(0)->nodeValue;
		$json = json_decode($json);

		// now make a dom parser which can parse the contents of the HTML tag
		$html = $dom->documentElement->getElementsByTagName("html")->item(0)->nodeValue;
		// replace all "&" with "&amp;" so it can be parsed
		$html = str_replace("&", "&amp;", $html);
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		
		$results = array();
		
		foreach( $json->messages as $mid=>$convo )
		{
			$elements = $xpath->query("//div[@id='$mid']");
			if(!is_null($elements))
			{
				if( $convo->isRead == false )
				{
					$element = $elements->item(0);
					$XMsg = $xpath->query("//div[@class='gc-message-message-display']", $element);
					$XMsgPart = $xpath->query("span|a", $XMsg->item(0));
					
					$msgText = '';
					$msgWords = array();
					foreach($XMsgPart as $m)
					{
						$word = $this->_unhtmlentities(trim($m->nodeValue));
						$msgText .= $word . ' ';
						if(preg_match('/gc-word-(.+)/', $m->attributes->getNamedItem('class')->textContent, $match))
							$confidence = $match[1];
						else
							$confidence = '';
						$msgWords[] = array('word'=>$word, 'confidence'=>$confidence);
					}
						
					$results[] = array('msgID'=>$mid, 'phoneNumber'=>$convo->phoneNumber, 'message'=>$msgText, 'date'=>date('Y-m-d H:i:s', intval($convo->startTime/1000)), 'words'=>$msgWords);
				}
				else
				{
					echo "This message ($mid) is not unread\n";
				}
			}
			else
			{
				echo "Could not find HTML version of message: $mid\n";
			}
		}
		
		return $results;
	}
	
	public function dom_dump($obj) {
		if ($classname = get_class($obj)) {
			$retval = "Instance of $classname, node list: \n";
			switch (true) {
				case ($obj instanceof DOMDocument):
					$retval .= "XPath: {$obj->getNodePath()}\n".$obj->saveXML($obj);
					break;
				case ($obj instanceof DOMElement):
					$retval .= "XPath: {$obj->getNodePath()}\n".$obj->ownerDocument->saveXML($obj);
					break;
				case ($obj instanceof DOMAttr):
					$retval .= "XPath: {$obj->getNodePath()}\n".$obj->ownerDocument->saveXML($obj);
					break;
				case ($obj instanceof DOMNodeList):
					for ($i = 0; $i < $obj->length; $i++) {
						$retval .= "Item #$i, XPath: {$obj->item($i)->getNodePath()}\n"."{$obj->item($i)->ownerDocument->saveXML($obj->item($i))}\n";
					}
					break;
				default:
					return "Instance of unknown class";
			}
		} else {
			return 'no elements...';
		}
		return htmlspecialchars($retval);
	}
	
	private function _unhtmlentities($string)
	{
		// replace numeric entities
		$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
		$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
		// replace literal entities
		$string = html_entity_decode($string);
		return $string;
	}
}

?>
