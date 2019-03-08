<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\NextCloudApi\Modules;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Client;
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\Utility\Files;

class Gallery
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @Flow\Inject
     * @var PsrSystemLoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    private $assetSourceSettings;

    public function __construct(array $assetSourceSettings)
    {
        $this->assetSourceSettings = $assetSourceSettings;
        $this->client = new Client([
            'auth' => [$assetSourceSettings['server']['userName'], $assetSourceSettings['server']['password']],
            'headers' => ['OCS-APIRequest: true']
        ]);
    }

    /**
     * @param int $fileId
     * @param int $width
     * @param int $height
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPreview(int $fileId, int $width, int $height): string
    {
        $url = Files::concatenatePaths([
                $this->assetSourceSettings['server']['baseUri'], 'apps/gallery/preview'
            ]) . sprintf('/%s?width=%s&height=%s', $fileId, $width, $height);

        $time = microtime(true);
        $data = $this->client->get($url)->getBody()->getContents();
        $this->logger->debug('Getting Preview ' . $fileId . ' lasts ' . (string)(microtime(true) - $time));
        return $data;
    }
}
