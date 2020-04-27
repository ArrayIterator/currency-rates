<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates\Provider;

use ArrayIterator\ProjectModule\CurrencyRates\AbstractProvider;
use ArrayIterator\ProjectModule\CurrencyRates\Rate;
use ArrayIterator\ProjectModule\CurrencyRates\Rates;
use ArrayIterator\ProjectModule\CurrencyRates\Util\DesktopUserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Class MayBankIndonesia
 * @package ArrayIterator\ProjectModule\CurrencyRates\Provider
 */
class MayBankIndonesia extends AbstractProvider
{
    /**
     * @var string
     */
    protected $providerCode = 'MayBank';

    /**
     * @var string
     */
    protected $providerName = 'MayBank Indonesia';

    /**
     * @var string
     */
    protected $providerUrl = 'https://www.maybank.co.id/';

    /**
     * @var string
     */
    protected $ratesUrl = 'https://www.maybank.co.id/Business/forexrate';

    /**
     * @var Rates
     */
    protected $rates;

    /**
     * @return bool
     */
    public function isBank(): bool
    {
        return true;
    }

    /**
     * @return Rates
     */
    public function getRates(): Rates
    {
        if ($this->rates instanceof Rates) {
            return $this->rates;
        }

        $this->rates = new Rates($this);
        $this->rates->addRate(new Rate($this->getBaseCode(), 1.0, 1.0, 1.0));
        $client = new Client([
            RequestOptions::HEADERS => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,'
                    .'image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                'Cache-Control' => 'max-age=0',
                'referer' => $this->getProviderUrl(),
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'cross-site',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
                'User-Agent' => (new DesktopUserAgent())->chrome(DesktopUserAgent::VERSION_LATEST)
            ],
            RequestOptions::VERIFY => false,
            RequestOptions::FORCE_IP_RESOLVE => 'v4',
        ]);
        try {
            $response = $client->get($this->ratesUrl);
            $body = (string) $response->getBody();
            $response->getBody()->close();
            unset($response);
            $body = HtmlPageCrawler::create($body);
            $tbody = $body->filter('#wrapper .row .stock-market table tr');
            if (!$tbody->count()) {
                unset($tbody);
                return $this->rates;
            }
            $tbody->each(function (HtmlPageCrawler $crawler) {
                $td = $crawler->filter('td');
                if ($td->count() < 3) {
                    return;
                }

                $code = $this->normalizeCurrency($td->eq(0)->text());
                if (strlen($code) !== 3
                    || $code === $this->getBaseCode()
                    || !preg_match('~[0-9]~', $td->eq(1)->text())
                    || !preg_match('~[0-9]~', $td->eq(2)->text())
                ) {
                    return;
                }
                $buy = str_replace('.', '', trim($td->eq(1)->text()));
                $sell = str_replace('.', '', trim($td->eq(2)->text()));
                $buy = abs(str_replace(',', '.', $buy));
                $sell = abs(str_replace(',', '.', $sell));

                $this->rates->addRate(new Rate($code, (float) $sell, (float) $sell, (float) $buy));
            });
        } catch (BadResponseException $e) {
        }

        return $this->rates;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseCode(): string
    {
        return 'IDR';
    }
}
