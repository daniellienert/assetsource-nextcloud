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
use Neos\Flow\Annotations as Flow;
use DL\AssetSource\NextCloud\Exception\NextCloudAssetSourceException;
use DL\AssetSource\NextCloud\NextCloudApi\NextCloudClient;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Service\FileTypeIconService;

final class NextCloudAssetSource implements AssetSourceInterface
{

    /**
     * @var mixed[]
     */
    private $assetSourceOptions;

    /**
     * @var string
     */
    private $assetSourceIdentifier;

    /**
     * @var AssetProxyRepositoryInterface
     */
    private $assetProxyRepository;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $maxItemLimit;

    /**
     * @var NextCloudClient
     */
    private $nextCloudClient;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @Flow\Inject
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @throws NextCloudAssetSourceException
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
        $this->assetSourceOptions = $assetSourceOptions;

        $this->label = $this->assetSourceOptions['label'] ?? 'Nextcloud';
        $this->maxItemLimit = $this->assetSourceOptions['maxItemLimit'] ?? 300;

        if (empty($this->assetSourceOptions['server']) || empty($this->assetSourceOptions['server']['baseUri']) || empty($this->assetSourceOptions['server']['userName']) || empty($this->assetSourceOptions['server']['password'])) {
            throw new NextCloudAssetSourceException('The given server configuration is not complete.');
        }

        $this->nextCloudClient = new NextCloudClient($this->assetSourceOptions);
    }

    public function initializeObject()
    {
        $this->uriBuilder->setRequest($this->createActionRequest());
    }

    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     * @throws NextCloudAssetSourceException
     */
    public static function createFromConfiguration(string $assetSourceIdentifier, array $assetSourceOptions): AssetSourceInterface
    {
        return new static($assetSourceIdentifier, $assetSourceOptions);
    }

    /**
     * A unique string which identifies the concrete asset source.
     * Must match /^[a-z][a-z0-9-]{0,62}[a-z]$/
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getMaxItemLimit(): int
    {
        return $this->maxItemLimit;
    }

    /**
     * @return NextCloudClient
     */
    public function getNextCloudClient(): NextCloudClient
    {
        return $this->nextCloudClient;
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new NextCloudAssetProxyRepository($this);
        }

        return $this->assetProxyRepository;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * @param NextCloudAsset $nextCloudAsset
     * @param int $width
     * @param int $height
     * @return Uri
     * @throws MissingActionNameException
     */
    public function getThumbnailUrl(NextCloudAsset $nextCloudAsset, int $width, int $height): Uri
    {
        if (!$this->nextcloudSupportsThumbnailGeneration($nextCloudAsset)) {
            $icon = FileTypeIconService::getIcon($nextCloudAsset->getFileName());
            return new Uri($this->resourceManager->getPublicPackageResourceUriByPath($icon['src']));
        }

        $arguments = [
            'assetSourceIdentifier' => $this->getIdentifier(),
            'fileId' => $nextCloudAsset->getFileId(),
            'width' => $width,
            'height' => $height
        ];

        return new Uri($this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('thumbnail', $arguments, 'Thumbnail', 'DL.AssetSource.NextCloud')
        );
    }

    /**
     * @param NextCloudAsset $nextCloudAsset
     * @return bool
     */
    private function nextcloudSupportsThumbnailGeneration(NextCloudAsset $nextCloudAsset): bool
    {
        $supportedTypes = $this->assetSourceOptions['enabledPreviewProviders'] ?? [];
        foreach ($supportedTypes as $nextCloudProvider => $mimeTypeRegex) {
            if (preg_match($mimeTypeRegex, $nextCloudAsset->getContentType())) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ActionRequest|null
     */
    private function createActionRequest(): ?ActionRequest
    {
        $requestHandler = $this->bootstrap->getActiveRequestHandler();
        if ($requestHandler instanceof HttpRequestHandlerInterface) {
            return new ActionRequest($requestHandler->getHttpRequest());
        }
        return null;
    }
}
