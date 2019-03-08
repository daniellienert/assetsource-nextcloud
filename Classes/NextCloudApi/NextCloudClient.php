<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\NextCloudApi;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */


use DL\AssetSource\NextCloud\NextCloudApi\Modules\Gallery;
use DL\AssetSource\NextCloud\NextCloudApi\WebDav\WebDavApi;

class NextCloudClient
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
     * @var Gallery
     */
    protected $gallery = null;

    public function __construct(array $assetSourceSettings)
    {
        $this->assetSourceSettings = $assetSourceSettings;
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
