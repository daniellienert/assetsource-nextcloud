<?php
declare(strict_types=1);

namespace DL\AssetSource\Nextcloud\AssetSource;

/*
 * This file is part of the DL.AssetSource.Nextcloud package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSource\Nextcloud\Exception\NextcloudAssetSourceException;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\Tag;
use Sabre\HTTP\ClientHttpException;

final class NextcloudAssetProxyRepository implements AssetProxyRepositoryInterface
{

    /**
     * @var NextcloudAssetSource
     */
    private $assetSource;

    /**
     * @var AssetTypeFilter
     */
    private $assetTypeFilter;

    /**
     * @param NextcloudAssetSource $assetSource
     */
    public function __construct(NextcloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param string $identifier
     * @return AssetProxyInterface
     * @throws NextcloudAssetSourceException
     * @throws ClientHttpException
     */
    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        return new NextcloudAssetProxy($this->assetSource->getNextcloudClient()->webDav()->propfind($identifier), $this->assetSource);
    }

    /**
     * @param AssetTypeFilter $assetType
     * @throws \Exception
     */
    public function filterByType(AssetTypeFilter $assetType = null): void
    {
        $this->assetTypeFilter = $assetType;
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     */
    public function findAll(): AssetProxyQueryResultInterface
    {
        return (new NextcloudAssetProxyQuery($this->assetSource))->execute();
    }

    /**
     * @param string $searchTerm
     * @return AssetProxyQueryResultInterface
     */
    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        $query = new NextcloudAssetProxyQuery($this->assetSource);
        $query->setSearchTerm($searchTerm);
        return $query->execute();
    }

    /**
     * @param Tag $tag
     * @return AssetProxyQueryResultInterface
     * @throws \Exception
     */
    public function findByTag(Tag $tag): AssetProxyQueryResultInterface
    {
        throw new \Exception(__METHOD__ . 'is not yet implemented');
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws \Exception
     */
    public function findUntagged(): AssetProxyQueryResultInterface
    {
        throw new \Exception(__METHOD__ . 'is not yet implemented');
    }

    /**
     * Count all assets, regardless of tag or collection
     *
     * @return int
     * @throws \Exception
     */
    public function countAll(): int
    {
        // TODO: return something useful
        return 3000;
    }
}
