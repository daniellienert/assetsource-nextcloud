<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\AssetSource;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use DL\AssetSource\NextCloud\NextCloudApi\WebDav\Dto\NextCloudAsset;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class NextCloudAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface
{
    /**
     * @var mixed[]
     */
    protected $nextCloudAsset;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var NextCloudAssetSource
     */
    private $assetSource;

    /**
     * @param NextCloudAsset $nextCloudAsset
     * @param NextCloudAssetSource $assetSource
     */
    public function __construct(NextCloudAsset $nextCloudAsset, NextCloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
        $this->nextCloudAsset = $nextCloudAsset;
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
        return $this->nextCloudAsset->getPath();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return urldecode(pathinfo(urldecode($this->nextCloudAsset->getPath()), PATHINFO_FILENAME));
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->nextCloudAsset->getFileName();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface
    {
        return $this->nextCloudAsset->getLastModified();
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->nextCloudAsset->getContentLength();
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->nextCloudAsset->getContentType();
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
        return $this->assetSource->getThumbnailUrl($this->nextCloudAsset, 250, 250);
    }

    /**
     * @return null|UriInterface
     * @throws MissingActionNameException
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->assetSource->getThumbnailUrl($this->nextCloudAsset, 1000, 1000);
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $this->assetSource->getNextCloudClient()->webDav()->getFileContent($this->nextCloudAsset->getPath()));
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
