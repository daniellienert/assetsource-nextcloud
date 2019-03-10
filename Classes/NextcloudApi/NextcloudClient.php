<?php
declare(strict_types=1);

namespace DL\AssetSource\Nextcloud\NextcloudApi;

/*
 * This file is part of the DL.AssetSource.Nextcloud package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


use DL\AssetSource\Nextcloud\NextcloudApi\Core\CoreApi;
use DL\AssetSource\Nextcloud\NextcloudApi\Modules\Gallery;
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\WebDavApi;

class NextcloudClient
{

    /**
     * @var mixed[]
     */
    protected $assetSourceSettings;

    /**
     * @var WebDavApi
     */
    protected $webDavApi = null;

    /**
     * @var CoreApi
     */
    protected $coreApi = null;

    /**
     * @var Gallery
     */
    protected $gallery = null;

    public function __construct(array $assetSourceSettings)
    {
        $this->assetSourceSettings = $assetSourceSettings;
    }

    /**
     * @return CoreApi
     */
    public function core(): CoreApi
    {
        if ($this->coreApi === null) {
            $this->coreApi = new CoreApi($this->assetSourceSettings);
        }
        return $this->coreApi;
    }

    /**
     * @return WebDavApi
     */
    public function webDav(): WebDavApi
    {
        if ($this->webDavApi === null) {
            $this->webDavApi = new WebDavApi($this->assetSourceSettings);
        }
        return $this->webDavApi;
    }

    /**
     * @return Gallery
     */
    public function gallery(): Gallery
    {
        if ($this->gallery === null) {
            $this->gallery = new Gallery($this->assetSourceSettings);
        }
        return $this->gallery;
    }
}
