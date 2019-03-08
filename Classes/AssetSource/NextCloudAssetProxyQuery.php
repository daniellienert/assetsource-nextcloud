<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\AssetSource;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;

final class NextCloudAssetProxyQuery implements AssetProxyQueryInterface
{

    /**
     * @var NextCloudAssetSource
     */
    private $assetSource;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $searchTerm = '';

    /**
     * UnsplashAssetProxyQuery constructor.
     * @param NextCloudAssetSource $assetSource
     */
    public function __construct(NextCloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
        $this->limit = $this->assetSource->getMaxItemLimit();
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit > $this->assetSource->getMaxItemLimit() ? $this->assetSource->getMaxItemLimit() : $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     */
    public function execute(): AssetProxyQueryResultInterface
    {
        $searchResult = $this->assetSource->getNextCloudClient()->webDav()->search($this->searchTerm, $this->limit, $this->offset);
        return new NextCloudAssetProxyQueryResult($this, $searchResult, $this->assetSource);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        throw new \Exception(__METHOD__ . 'is not yet implemented');
    }
}
