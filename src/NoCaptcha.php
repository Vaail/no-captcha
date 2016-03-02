<?php namespace Vaail\NoCaptcha;

use Symfony\Component\HttpFoundation\Request;

class NoCaptcha {

	const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
	const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * //
	 * 
	 * @var string
	 */
	protected $secret;

	/**
	 * //
	 * 
	 * @var string
	 */
	protected $sitekey;

	/**
	 * //
	 * 
	 * @param string $secret
	 * @param string $sitekey
	 */
	public function __construct($secret, $sitekey)
	{
		$this->secret = $secret;
		$this->sitekey = $sitekey;
	}

	/**
	 * //
	 * 
	 * @return string
	 */
	public function display($attributes = ['auto' => true], $lang = null)
	{
		$attributes['data-sitekey'] = $this->sitekey;
		$renderAuto = false;
		$id = '';
		if(isset($attributes['auto']) && $attributes['auto'] === true) {
			$attributes['class'] = 'g-recaptcha';
			$renderAuto = true;
		} else {
			if(isset($attributes['id'])) {
				$id = $attributes['id'];
			}
		}

		$html = <<<JS
<script>
(function(document) {
  var elem;
  if(document.getElementById('recaptcha-js') === null) {
    elem = document.createElement('script');
    elem.setAttribute('id', 'recaptcha-js');
    elem.setAttribute('type', 'text/javascript');
    elem.setAttribute('src', '{$this->getJsLink($lang)}');
    elem.setAttribute('async', 'async');
    elem.setAttribute('defer', 'defer');
    if (typeof elem !== 'undefined') {
      document.getElementsByTagName("head")[0].appendChild(elem);
    }
  }
})(document);
</script>
JS;
		if(!$renderAuto && $id !== '') {
			$params = [];
			foreach($attributes as $key => $value) {
				$params[str_replace('data-', '', $key)] = $value;
			}
			$params = json_encode($params);

			$html .= <<<JS
<script>
(function() {
  function recaptchaIsLoaded() {
    return typeof grecaptcha !== 'undefined';
  }
  function waitRecaptcha(cb) {
    cb = typeof cb === 'function' ? cb : function() {};
    if(recaptchaIsLoaded()) {
      cb();
    } else {
      setTimeout(waitRecaptcha.bind(null, cb), 50);
    }
  }
  waitRecaptcha(function() {
  	var captchaId = grecaptcha.render('{$id}', JSON.parse('{$params}'));
  	document.getElementById('{$id}').dataset.recaptchaId = captchaId;
  });
})();
</script>
JS;
		}

		$html .= '<div '.$this->buildAttributes($attributes).'></div>';

		return $html;
	}

	/**
	 * //
	 * 
	 * @param  string $response
	 * @param  string $clientIp
	 * @return bool
	 */
	public function verifyResponse($response, $clientIp = null)
	{
		if (empty($response)) return false;

		$response = $this->sendRequestVerify([
			'secret'   => $this->secret,
			'response' => $response,
			'remoteip' => $clientIp
		]);

		return isset($response['success']) && $response['success'] === true;
	}

	/**
	 * //
	 * 
	 * @param  Request $request
	 * @return bool
	 */
	public function verifyRequest(Request $request)
	{
		return $this->verifyResponse(
			$request->get('g-recaptcha-response'),
			$request->getClientIp()
		);
	}

	/**
	 * //
	 * 
	 * @return string
	 */
	public function getJsLink($lang = null)
	{
		return $lang ? static::CLIENT_API.'?hl='.$lang : static::CLIENT_API;
	}

	/**
	 * //
	 * 
	 * @param  array  $query
	 * @return array
	 */
	protected function sendRequestVerify(array $query = [])
	{
		$link = static::VERIFY_URL.'?'.http_build_query($query);

		$response = file_get_contents($link);

		return json_decode($response, true);
	}

	/**
	 * //
	 * 
	 * @param  array  $attributes
	 * @return string
	 */
	protected function buildAttributes(array $attributes)
	{
		$html = [];

		foreach ($attributes as $key => $value)
		{
			$html[] = $key.'="'.$value.'"';
		}

		return count($html) ? ' '.implode(' ', $html) : '';
	}

}
