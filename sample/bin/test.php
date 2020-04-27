<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates\Bin;

use ArrayIterator\ProjectModule\CurrencyRates\AbstractProvider;
use ArrayIterator\ProjectModule\CurrencyRates\Exchange;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankBCA;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankBNI;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankDanamon;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankMandiri;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankMas;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\BankPanin;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\EcbEurope;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\MayBankIndonesia;
use ArrayIterator\ProjectModule\CurrencyRates\Provider\UOBIndonesia;

require __DIR__ .'/../../vendor/autoload.php';

$exchange = new Exchange();
/**
 * @var AbstractProvider $bank
 */
$bank = $exchange->provider(BankBCA::class);
$bank = $exchange->provider(BankBNI::class);
$bank = $exchange->provider(BankDanamon::class);
$bank = $exchange->provider(BankMandiri::class);
$bank = $exchange->provider(BankMas::class);
$bank = $exchange->provider(BankPanin::class);
$bank = $exchange->provider(EcbEurope::class);
$bank = $exchange->provider(MayBankIndonesia::class);
$bank = $exchange->provider(UOBIndonesia::class);
// just call bank rate to get real data for serialization
$rates = $bank->getRates();

// just check serialize & unserialized work properly
$bank = unserialize(serialize($bank));

// below must be equal data source
var_dump(json_encode($bank->getRates()) === json_encode($rates));
