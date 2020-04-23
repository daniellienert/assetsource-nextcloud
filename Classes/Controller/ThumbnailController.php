<?php
declare(strict_types=1);

namespace DL\AssetSource\Nextcloud\Controller;

/*
 * This file is part of the DL.AssetSource.Nextcloud package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSource\Nextcloud\AssetSource\NextcloudAssetSource;
use DL\AssetSource\Nextcloud\NextcloudApi\Core\CoreApi;
use DL\AssetSource\Nextcloud\NextcloudApi\Modules\Gallery;
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
     */
    public function thumbnailAction(string $assetSourceIdentifier, int $fileId, int $width, int $height): string
    {
        $this->response->setContentType('image/jpg');
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

        if ($assetSource instanceof NextcloudAssetSource) {
            return $assetSource->getNextcloudClient()->core();
        }
    }
}
