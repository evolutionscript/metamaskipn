# Metamask IPN with FIAT or Crypto

[MetamaskIPN](https://metamaskipn.com) allows you to receive instant payment notifications (IPN) in your site every time
you receive a payment from Metamask wallets.

This library allows you convert FIAT or other cryptocurrencies to the [supported tokens](https://metamaskipn.com/docs) of MetamaskIPN and generate
the payment button or shopping cart link.


**Installation:**

```bash
composer require evolutionscript/metamaskipn
```


**Usage:**

```php
use EvolutionScript\MetamaskIPN as MetamaskIPN;
//Initialize MetamaskIPN Class
$metamaskIPN = new MetamaskIPN\MetamaskIPN();

//Specify cache directory to save data from FIAT rates and make a request once per day. It is optional but prevents exceeding the API usage limit. 
$metamaskIPN->cacheDirectory(__DIR__.'/cache');

//Connect with Currency Layer and as optional connect with OpenExchangeRates. The optional provider is useful if the primary provider fails.
$metamaskIPN->currencyProviders(
	new \EvolutionScript\CurrencyAPI\Providers\CurrencyLayer('CURRENCY_LAYER_API'),
	new \EvolutionScript\CurrencyAPI\Providers\OpenExchangeRates('OPEN_EXCHANGE_RATES_API')
);


//If we are going to use fiat and deposit will be in Litecoin (LTC):
$button_code = $metamaskIPN->from_fiat(10, 'PEN')
	->to_crypto('LTC')
	->site_id(0)
	->custom_1('my_optional_parameter')
	->custom_2('other_option_parameter')
	->custom_3('another_option_parameter')
	->lang('en') //For supported languages go to https://metamaskipn.com/docs
	->button_code('Pay Now');

echo $button_code;

//If we are going to use a cryptocurrency like Solana (SOL) and deposit in in BTCB (BTC)
$button_code_2 = $metamaskIPN->from_crypto(1,'SOL')
	->to_crypto('BTCB')
	->site_id(0)
	->custom_1('custom_parameter')
	->lang('es')
	->button_code();
echo $button_code_2;

//If we are going to use the shopping cart URL, then we can specify item name and other items.
$shopping_car_url = $metamaskIPN->from_crypto(55,'XRP')
	->to_crypto('BNB')
	->site_id(0)
	->custom_1('custom_parameter')
	->lang('en')
	->item_name('Make a donation')
	->item_description('Support our project')
	->logo('https://www.evolutionscript.com/assets/evolution/images/logo.png') //URL of your logo
	->cancel_url('https://mysite.com/cart')
	->success_url('https://mysite.com/success')
	->shopping_cart();
echo '<br><a href="'.$shopping_car_url.'">Click here to redirect to shopping cart</a>';
```
