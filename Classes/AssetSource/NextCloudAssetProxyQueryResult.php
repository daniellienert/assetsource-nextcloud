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

use DL\AssetSource\NextCloud\NextCloudApi\WebDav\Dto\NextCloudAsset;
use DL\AssetSource\NextCloud\NextCloudApi\WebDav\Dto\SearchResult;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;

final class NextCloudAssetProxyQueryResult implements AssetProxyQueryResultInterface
{

    /**
     * @var NextCloudAssetSource
     */
    private $assetSource;

    /**
     * @var mixed[]
     */
    private $nextCloudSearchResult;

    /**
     * @var NextCloudAssetProxyQuery
     */
    private $nextCloudAssetProxyQuery;

    /**
     * @var \Iterator
     */
    private $nextCloudSearchResultIterator;

    /**
     * NextCloudAssetProxyQueryResult constructor.
     * @param NextCloudAssetProxyQuery $query
     * @param SearchResult $nextCloudSearchResult
     * @param NextCloudAssetSource $assetSource
     */
    public function __construct(NextCloudAssetProxyQuery $query, SearchResult $nextCloudSearchResult, NextCloudAssetSource $assetSource)
    {
        $this->nextCloudAssetProxyQuery = $query;
        $this->assetSource = $assetSource;
        $this->nextCloudSearchResult = $nextCloudSearchResult;
        $this->nextCloudSearchResultIterator = $nextCloudSearchResult->getAssetIterator();
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $asset = $this->nextCloudSearchResultIterator->current();

        if ($asset instanceof NextCloudAsset) {
            return new NextCloudAssetProxy($asset, $this->assetSource);
        } else {
            return null;
        }
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        return $this->nextCloudSearchResultIterator->next();
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->nextCloudSearchResultIterator->key();
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->nextCloudSearchResultIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->nextCloudSearchResultIterator->rewind();
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->nextCloudSearchResultIterator->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->nextCloudSearchResultIterator->offsetGet($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->nextCloudSearchResultIterator->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->nextCloudSearchResultIterator->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->nextCloudSearchResult->getTotalResults();
    }

    /**
     * Returns a clone of the query object
     *
     * @api
     */
    public function getQuery(): AssetProxyQueryInterface
    {
        return $this->nextCloudAssetProxyQuery;
    }

    /**
     * Returns the first object in the result set
     *
     * @return object
     * @api
     */
    public function getFirst(): ?AssetProxyInterface
    {
        return $this->offsetGet(0);
    }

    /**
     * Returns an array with the objects in the result set
     *
     * @return array
     * @api
     */
    public function toArray(): array
    {
        return $this->nextCloudSearchResult->getAssets()->getArrayCopy();
    }
}
