<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\NextCloudApi\Core;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Client;
use Neos\Flow\Log\PsrSystemLoggerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Service\FileTypeIconService;
use Neos\Utility\Files;
use Psr\Http\Message\ResponseInterface;

class CoreApi
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
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string[]
     */
    private $assetSourceSettings;

    public function __construct(array $assetSourceSettings)
    {
        $this->assetSourceSettings = $assetSourceSettings;
        $this->client = new Client([
            'auth' => [$assetSourceSettings['server']['userName'], $assetSourceSettings['server']['password']],
            'http_errors' => false
        ]);
    }

    /**
     * @param int $fileId
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getPreview(int $fileId, int $width, int $height): string
    {
        $url = Files::concatenatePaths([$this->assetSourceSettings['server']['baseUri'], 'core/preview']) . sprintf('?fileId=%s&x=%s&y=%s', $fileId, $width, $height);
        $response = null;

        $time = microtime(true);
        $response = $this->client->get($url);
        $this->logger->debug('Getting Preview ' . $fileId . ' lasts ' . (string)(microtime(true) - $time));
        return $response->getBody()->getContents();
    }
}
