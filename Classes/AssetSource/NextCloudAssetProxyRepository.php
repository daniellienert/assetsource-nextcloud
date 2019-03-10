<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\AssetSource;

/*
 * This file is part of the DL.AssetSource.NextCloud package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSource\NextCloud\Exception\NextCloudAssetSourceException;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\Tag;
use Sabre\HTTP\ClientHttpException;

final class NextCloudAssetProxyRepository implements AssetProxyRepositoryInterface
{

    /**
     * @var NextCloudAssetSource
     */
    private $assetSource;

    /**
     * @var AssetTypeFilter
     */
    private $assetTypeFilter;

    /**
     * @param NextCloudAssetSource $assetSource
     */
    public function __construct(NextCloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param string $identifier
     * @return AssetProxyInterface
     * @throws NextCloudAssetSourceException
     * @throws ClientHttpException
     */
    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        return new NextCloudAssetProxy($this->assetSource->getNextCloudClient()->webDav()->propfind($identifier), $this->assetSource);
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
        return (new NextCloudAssetProxyQuery($this->assetSource))->execute();
    }

    /**
     * @param string $searchTerm
     * @return AssetProxyQueryResultInterface
     */
    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        $query = new NextCloudAssetProxyQuery($this->assetSource);
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
