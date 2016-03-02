No CAPTCHA reCAPTCHA
==========

![recaptcha_anchor 2x](https://cloud.githubusercontent.com/assets/1529454/5291635/1c426412-7b88-11e4-8d16-46161a081ece.gif)

## Installation

Add the following line to the `require` section of `composer.json`:

```json
{
    "require": {
        "vaail/no-captcha": "1.*"
    }
}
```

Run `composer update`.

## Laravel 5

### Setup

Add ServiceProvider to the providers array in `app/config/app.php`.

```
'Vaail\NoCaptcha\NoCaptchaServiceProvider',
```

### Configuration

Add `NOCAPTCHA_SECRET` and `NOCAPTCHA_SITEKEY` in **.env** file:

```
NOCAPTCHA_SECRET=[secret-key]
NOCAPTCHA_SITEKEY=[site-key]
```

### Usage

##### Display reCAPTCHA

```php
{!! app('captcha')->display(); !!}
```

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php

$validate = Validator::make(Input::all(), [
	'g-recaptcha-response' => 'required|captcha'
]);

```

## Without Laravel

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$secret  = '';
$sitekey = '';
$captcha = new \Vaail\NoCaptcha\NoCaptcha($secret, $sitekey);

if ( ! empty($_POST)) {
    var_dump($captcha->verifyResponse($_POST['g-recaptcha-response']));
    exit();
}

?>

<form action="?" method="POST">
    <?php echo $captcha->display(); ?>
    <button type="submit">Submit</button>
</form>

```

## Contribute

https://github.com/vaail/no-captcha/pulls
