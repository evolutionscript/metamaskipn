<?php
namespace EvolutionScript\MetamaskIPN;


use EvolutionScript\CryptocurrencyAPI\Exchange;
use EvolutionScript\CurrencyAPI\Rate;

class MetamaskIPN
{
	private $currency_converter;
	private $crypto_api;
	private $base_currency = 'USD';
	private $fiat_amount = 0;
	public $deposit_amount = 0;
	private $site_id = 0;
	private $token = '';
	private $custom_1 = null;
	private $custom_2 = null;
	private $custom_3 = null;
	private $lang = 'en';
	private $item_name = '';
	private $item_description = '';
	private $logo = '';
	private $cancel_url = '';
	private $success_url = '';

	public function __construct()
	{
		$this->currency_converter = new Rate();
		$this->crypto_api = new Exchange();
	}

	public function cacheDirectory($cache_dir)
	{
		$this->currency_converter->cacheDirectory($cache_dir);
	}

	public function currencyProviders($service_provider, $backup_provider=null)
	{
		$this->currency_converter->provider($service_provider, $backup_provider);
	}

	public function base_currency($base_currency)
	{
		$this->base_currency = strtoupper($base_currency);
	}

	public function from_fiat($amount, $currency)
	{
		$currency = strtoupper($currency);
		$rate = $this->currency_converter->getRate($this->base_currency, $currency);
		$this->fiat_amount = $this->currency_converter->exchangeTo($rate, $amount);
		return $this;
	}

	public function from_crypto($amount, $currency)
	{
		if(in_array($currency, ['USDT','USD','BUSD','USDC','DAI'])){
			$this->fiat_amount = $amount;
		}else{
			$rate = $this->crypto_api->rate($currency, 'USDT');
			$this->fiat_amount = $this->crypto_api->exchangeTo($amount, $rate);
		}
		return $this;
	}

	public function to_crypto($token)
	{
		if(!array_key_exists($token, $this->getTokens())){
			throw new \Exception('Token is not supported by MetamaskIPN.');
		}
		$this->token = $token;
		$crypto_currency = $this->getTokens()[$token];
		if($crypto_currency == 'USD'){
			$this->deposit_amount = $this->fiat_amount;
		}else{
			$rate = $this->crypto_api->rate($crypto_currency, 'USDT');
			//1 Crypto = $rate
			$this->deposit_amount = $this->crypto_api->exchangeFrom($this->fiat_amount, $rate);
		}
		return $this;
	}
	private function getTokens()
	{
		return [
			'BNB' => 'BNB',
			'BUSD' => 'USD',
			'BTCB' => 'BTC',
			'ETH' => 'ETH',
			'LTC' => 'LTC',
			'USDT' => 'USD',
			'Cake' => 'CAKE',
			'UNI' => 'UNI'
		];
	}

	public function site_id($site_id)
	{
		if(!isset($site_id) || !is_numeric($site_id)){
			throw new \Exception('MetamaskIPN site ID is not valid.');
		}
		$this->site_id = $site_id;
		return $this;
	}

	public function custom_1($custom_1)
	{
		$this->custom_1 = $custom_1;
		return $this;
	}

	public function custom_2($custom_2)
	{
		$this->custom_2 = $custom_2;
		return $this;
	}

	public function custom_3($custom_3)
	{
		$this->custom_3 = $custom_3;
		return $this;
	}

	public function lang($lang)
	{
		$this->lang = $lang;
		return $this;
	}

	public function item_name($item_name)
	{
		$this->item_name = $item_name;
		return $this;
	}

	public function item_description($item_description)
	{
		$this->item_description = $item_description;
		return $this;
	}

	public function logo($logo)
	{
		$this->logo = $logo;
		return $this;
	}

	public function cancel_url($cancel_url)
	{
		$this->cancel_url = $cancel_url;
		return $this;
	}

	public function success_url($success_url)
	{
		$this->success_url = $success_url;
		return $this;
	}

	public function button_code($button_text='', $reset=true)
	{
		$params['site'] = $this->site_id;
		$params['token'] = $this->token;
		$params['lang'] = $this->lang;
		if(!is_null($this->custom_1) || $this->custom_1 != ''){
			$params['custom_1'] = $this->custom_1;
		}
		if(!is_null($this->custom_2) || $this->custom_2 != ''){
			$params['custom_2'] = $this->custom_2;
		}
		if(!is_null($this->custom_3) || $this->custom_3 != ''){
			$params['custom_3'] = $this->custom_3;
		}
		if($button_text != ''){
			$params['btn_txt'] = $button_text;
		}
		if($this->deposit_amount <= 0){
			throw new \Exception('Invalid deposit amount.');
		}
		$params['amount'] = $this->deposit_amount;
		$query = http_build_query($params);
		$code = '<iframe src="https://metamaskipn.com/button?'.$query.'" style="border: 0; overflow: hidden"></iframe>';
		if($reset){
			$this->reset_params();
		}
		return $code;
	}

	public function shopping_cart()
	{
		$params['site'] = $this->site_id;
		$params['token'] = $this->token;
		$params['lang'] = $this->lang;
		if(!is_null($this->custom_1) || $this->custom_1 != ''){
			$params['custom_1'] = $this->custom_1;
		}
		if(!is_null($this->custom_2) || $this->custom_2 != ''){
			$params['custom_2'] = $this->custom_2;
		}
		if(!is_null($this->custom_3) || $this->custom_3 != ''){
			$params['custom_3'] = $this->custom_3;
		}
		if($this->item_name == ''){
			throw new \Exception('item_name is required.');
		}
		$params['item_name'] = $this->item_name;
		if($this->item_description !== ''){
			$params['item_description'] = $this->item_description;
		}
		if(filter_var($this->logo, FILTER_VALIDATE_URL)){
			$params['logo'] = $this->logo;
		}
		if(filter_var($this->cancel_url, FILTER_VALIDATE_URL)){
			$params['cancel_url'] = $this->cancel_url;
		}
		if(filter_var($this->success_url, FILTER_VALIDATE_URL)){
			$params['success_url'] = $this->success_url;
		}
		if($this->deposit_amount <= 0){
			throw new \Exception('Invalid deposit amount.');
		}
		$params['amount'] = $this->deposit_amount;
		$query = http_build_query($params);
		$code = 'https://metamaskipn.com/cart?'.$query;
		$this->reset_params();
		return $code;
	}

	public function reset_params()
	{
		$this->fiat_amount = 0;
		$this->deposit_amount = 0;
		$this->site_id = 0;
		$this->token = '';
		$this->custom_1 = null;
		$this->custom_2 = null;
		$this->custom_3 = null;
		$this->lang = 'en';
	}
}
