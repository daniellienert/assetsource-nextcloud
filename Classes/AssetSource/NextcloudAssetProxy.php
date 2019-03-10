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

use Neos\Flow\Annotations as Flow;
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\Dto\NextcloudAsset;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class NextcloudAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface
{
    /**
     * @var mixed[]
     */
    protected $NextcloudAsset;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var NextcloudAssetSource
     */
    private $assetSource;

    /**
     * @param NextcloudAsset $NextcloudAsset
     * @param NextcloudAssetSource $assetSource
     */
    public function __construct(NextcloudAsset $NextcloudAsset, NextcloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
        $this->NextcloudAsset = $NextcloudAsset;
        $this->importedAsset = (new ImportedAssetRepository())->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSource->getIdentifier(), $this->getIdentifier());
    }

    /**
     * @return AssetSourceInterface
     */
    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->NextcloudAsset->getPath();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return urldecode(pathinfo(urldecode($this->NextcloudAsset->getPath()), PATHINFO_FILENAME));
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->NextcloudAsset->getFileName();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface
    {
        return $this->NextcloudAsset->getLastModified();
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->NextcloudAsset->getContentLength();
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->NextcloudAsset->getContentType();
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getHeightInPixels(): ?int
    {
        return null;
    }

    /**
     * @return null|UriInterface
     * @throws MissingActionNameException
     */
    public function getThumbnailUri(): ?UriInterface
    {
        return $this->assetSource->getThumbnailUrl($this->NextcloudAsset, 250, 250);
    }

    /**
     * @return null|UriInterface
     * @throws MissingActionNameException
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->assetSource->getThumbnailUrl($this->NextcloudAsset, 1000, 1000);
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $this->assetSource->getNextcloudClient()->webDav()->getFileContent($this->NextcloudAsset->getPath()));
        rewind($stream);
        return $stream;
    }

    /**
     * @return null|string
     */
    public function getLocalAssetIdentifier(): ?string
    {
        return $this->importedAsset instanceof ImportedAsset ? $this->importedAsset->getLocalAssetIdentifier() : '';
    }


    /**
     * Returns true if the binary data of the asset has already been imported into the Neos asset source.
     *
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->importedAsset !== null;
    }
}
