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
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\WebDavApi;
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\Dto\NextcloudAsset;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class NextcloudAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface, SupportsIptcMetadataInterface
{
    /**
     * @var mixed[]
     */
    protected $nextcloudAsset;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var NextcloudAssetSource
     */
    private $assetSource;

    /**
     * @var array
     */
    private $iptcProperties = null;

    /**
     * @param NextcloudAsset $NextcloudAsset
     * @param NextcloudAssetSource $assetSource
     */
    public function __construct(NextcloudAsset $NextcloudAsset, NextcloudAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
        $this->nextcloudAsset = $NextcloudAsset;
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
        return str_replace(WebDavApi::ENDPOINT_URL_PART, '', $this->nextcloudAsset->getPath());
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return urldecode(pathinfo(urldecode($this->nextcloudAsset->getPath()), PATHINFO_FILENAME));
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->nextcloudAsset->getFileName();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface
    {
        return $this->nextcloudAsset->getLastModified();
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->nextcloudAsset->getContentLength();
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->nextcloudAsset->getContentType();
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
        return $this->assetSource->getThumbnailUrl($this->nextcloudAsset, 250, 250);
    }

    /**
     * @return null|UriInterface
     * @throws MissingActionNameException
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->assetSource->getThumbnailUrl($this->nextcloudAsset, 1000, 1000);
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $this->assetSource->getNextcloudClient()->webDav()->getFileContent($this->nextcloudAsset->getPath()));
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

    /**
     * Returns true, if the given IPTC metadata property is available, ie. is supported and is not empty.
     *
     * @param string $propertyName
     * @return bool
     * @throws \Neos\Eel\Exception
     */
    public function hasIptcProperty(string $propertyName): bool
    {
        return isset($this->getIptcProperties()[$propertyName]);
    }

    /**
     * Returns the given IPTC metadata property if it exists, or an empty string otherwise.
     *
     * @param string $propertyName
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperty(string $propertyName): string
    {
        return $this->getIptcProperties()[$propertyName] ?? '';
    }

    /**
     * Returns all known IPTC metadata properties as key => value (e.g. "Title" => "My Photo")
     *
     * @return array
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperties(): array
    {
        if ($this->iptcProperties === null) {
            $this->iptcProperties = [
                'Title' => $this->getLabel(),
            ];
        }

        return $this->iptcProperties;
    }
}
