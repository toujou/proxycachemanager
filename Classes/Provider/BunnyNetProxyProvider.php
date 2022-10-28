<?php

declare(strict_types=1);
namespace B13\Proxycachemanager\Provider;

/*
 * This file is part of the b13 TYPO3 extensions family.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Uses bunny.net API with API AccessKey.
 * https://api.bunny.net/
 *
 * Ensure to set the environment variable BUNNY_ACCESSKEY_TOKEN.
 */
class BunnyNetProxyProvider implements ProxyProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Client */
    protected $client;

    /** @var string[] */
    protected $sites;

    public function __construct(Client $client, array $sites)
    {
        $this->client = $client;
        $this->sites = $sites;
    }

    /**
     * @inheritDoc
     */
    public function setProxyEndpoints($endpoints)
    {
        // not necessary
    }

    /**
     * @inheritDoc
     */
    public function flushCacheForUrl($url)
    {
        $this->purgeMultipleUrls([$url]);
    }

    /**
     * @inheritDoc
     */
    public function flushAllUrls($urls = [])
    {
        if ($urls === []) {
            $urls = $this->sites;
        }
        $this->purgeMultipleUrls($urls, true);
    }

    /**
     * @inheritDoc
     */
    public function flushCacheForUrls(array $urls)
    {
        $this->purgeMultipleUrls($urls);
    }

    private function purgeMultipleUrls(array $urls, bool $appendWildcard = false): void
    {
        foreach ($urls as $url) {
            try {
                $this->client->post('', [
                    'query' => ['url' => $url . ($appendWildcard ? '*': ''),]
                ]);
            } catch (TransferException $e) {
                $this->logger->error('Could not flush URL via POST', [
                    'url' => $url,
                    'exception' => $e,
                ]);
            }
        }
    }
}
