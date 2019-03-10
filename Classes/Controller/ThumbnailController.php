<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\Controller;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use DL\AssetSource\NextCloud\AssetSource\NextCloudAssetSource;
use DL\AssetSource\NextCloud\NextCloudApi\Core\CoreApi;
use DL\AssetSource\NextCloud\NextCloudApi\Modules\Gallery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Media\Domain\Service\AssetSourceService;

class ThumbnailController extends ActionController
{
    /**
     * @Flow\Inject
     * @var AssetSourceService
     */
    protected $assetSourceService;

    /**
     * @param string $assetSourceIdentifier
     * @param int $fileId
     * @param int $width
     * @param int $height
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function thumbnailAction(string $assetSourceIdentifier, int $fileId, int $width, int $height): string
    {
        $this->response->setHeader('Content-type', 'image/jpg');
        return $this->getCoreApi($assetSourceIdentifier)->getPreview($fileId, $width, $height);
    }

    /**
     * @param string $assetSourceIdentifier
     * @return CoreApi
     */
    protected function getCoreApi(string $assetSourceIdentifier): CoreApi
    {
        $assetSources = $this->assetSourceService->getAssetSources();
        $assetSource = $assetSources[$assetSourceIdentifier];

        if ($assetSource instanceof NextCloudAssetSource) {
            return $assetSource->getNextCloudClient()->core();
        }
    }
}
