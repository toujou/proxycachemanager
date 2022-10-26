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

use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ToshY\BunnyNet\BaseRequest;

/**
 * Uses bunny.net API with API AccessKey.
 * https://api.bunny.net/
 *
 * Ensure to set the environment variable BUNNY_ACCESSKEY_TOKEN.
 */
class BunnyProxyProvider implements ProxyProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var BaseRequest
     */
    protected $client;

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
        if (!$this->isActive()) {
            return;
        }

        try {
            $this->getClient()->purgeUrl([
                'url' => $url,
            ]);
        } catch (TransferException $e) {
            $this->logger->error('Could not flush URLs for {zone} via POST "purge_cache"', [
                'urls' => $url,
                'exception' => $e,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function flushAllUrls($urls = [])
    {
        if (!$this->isActive()) {
            return;
        }

        $this->purgeMulitpleUrls($urls, true);
    }

    /**
     * @inheritDoc
     */
    public function flushCacheForUrls(array $urls)
    {
        if (!$this->isActive()) {
            return;
        }

        $this->purgeMulitpleUrls($urls);
    }

    public function purgeMulitpleUrls(array $urls, bool $appendWildcard = false)
    {
        foreach ($urls as $url) {
            try {
                $this->getClient()->purgeUrl([
                    'url' => $url . ($appendWildcard ? '*': ''),
                ]);
            } catch (TransferException $e) {
                $this->logger->error('Could not flush URLs for {zone} via POST "purge_cache"', [
                    'urls' => $url,
                    'exception' => $e,
                ]);
            }
        }
    }

    protected function isActive(): bool
    {
        return !empty(getenv('BUNNY_ACCESSKEY_TOKEN'));
    }

    protected function getClient(): BaseRequest
    {
        $this->client = new BaseRequest(getenv('BUNNY_ACCESSKEY_TOKEN'));
        return $this->client;
    }
}
