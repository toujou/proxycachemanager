<?php

declare(strict_types=1);

namespace B13\Proxycachemanager\Factory;


use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteFactory
{
    /**
     * @return string[]
     */
    public function getAllSitesBase(): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();

        $siteUrls = [];
        foreach ($sites as $site) {
            $siteUrls[] = (string) $site->getBase();
        }
        return $siteUrls;
    }
}
