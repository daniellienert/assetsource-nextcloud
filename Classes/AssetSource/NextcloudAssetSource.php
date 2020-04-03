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

use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\Dto\NextcloudAsset;
use Neos\Flow\Annotations as Flow;
use DL\AssetSource\Nextcloud\Exception\NextcloudAssetSourceException;
use DL\AssetSource\Nextcloud\NextcloudApi\NextcloudClient;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionRequestFactory;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Http\Factories\UriFactory;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Service\FileTypeIconService;
use Psr\Http\Message\UriInterface;

final class NextcloudAssetSource implements AssetSourceInterface
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
     * @var NextcloudClient
     */
    private $nextcloudClient;

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
     * @Flow\Inject
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @Flow\Inject
     * @var ActionRequestFactory
     */
    protected $actionRequestFactory;


    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @throws NextcloudAssetSourceException
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
        $this->assetSourceOptions = $assetSourceOptions;

        $this->label = $this->assetSourceOptions['label'] ?? 'Nextcloud';
        $this->maxItemLimit = $this->assetSourceOptions['maxItemLimit'] ?? 300;

        if (empty($this->assetSourceOptions['server']) || empty($this->assetSourceOptions['server']['baseUri']) || empty($this->assetSourceOptions['server']['userName']) || empty($this->assetSourceOptions['server']['password'])) {
            throw new NextcloudAssetSourceException('The given server configuration is not complete.');
        }

        $this->nextcloudClient = new NextcloudClient($this->assetSourceOptions);
    }

    public function initializeObject(): void
    {
        $this->uriBuilder->setRequest($this->createActionRequest());
    }

    /**
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     * @throws NextcloudAssetSourceException
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
     * @return NextcloudClient
     */
    public function getNextcloudClient(): NextcloudClient
    {
        return $this->nextcloudClient;
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new NextcloudAssetProxyRepository($this);
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
     * @param NextcloudAsset $NextcloudAsset
     * @param int $width
     * @param int $height
     * @return Uri
     * @throws MissingActionNameException
     * @throws \Neos\Flow\Http\Exception
     */
    public function getThumbnailUrl(NextcloudAsset $NextcloudAsset, int $width, int $height): UriInterface
    {
        if (!$this->nextcloudSupportsThumbnailGeneration($NextcloudAsset)) {
            $icon = FileTypeIconService::getIcon($NextcloudAsset->getFileName());
            return $this->uriFactory->createUri($this->resourceManager->getPublicPackageResourceUriByPath($icon['src']));
        }

        $arguments = [
            'assetSourceIdentifier' => $this->getIdentifier(),
            'fileId' => $NextcloudAsset->getFileId(),
            'width' => $width,
            'height' => $height
        ];

        return $this->uriFactory->createUri($this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('thumbnail', $arguments, 'Thumbnail', 'DL.AssetSource.Nextcloud')
        );
    }

    /**
     * @param NextcloudAsset $NextcloudAsset
     * @return bool
     */
    private function nextcloudSupportsThumbnailGeneration(NextcloudAsset $NextcloudAsset): bool
    {
        $supportedTypes = $this->assetSourceOptions['enabledPreviewProviders'] ?? [];
        foreach ($supportedTypes as $NextcloudProvider => $mimeTypeRegex) {
            if (preg_match($mimeTypeRegex, $NextcloudAsset->getContentType())) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ActionRequest|null
     * @throws \Neos\Flow\Mvc\Exception\InvalidActionNameException
     * @throws \Neos\Flow\Mvc\Exception\InvalidArgumentNameException
     * @throws \Neos\Flow\Mvc\Exception\InvalidArgumentTypeException
     * @throws \Neos\Flow\Mvc\Exception\InvalidControllerNameException
     */
    private function createActionRequest(): ?ActionRequest
    {
        $requestHandler = $this->bootstrap->getActiveRequestHandler();
        if ($requestHandler instanceof HttpRequestHandlerInterface) {
            return $this->actionRequestFactory->createActionRequest($requestHandler->getComponentContext()->getHttpRequest());
        }
        return null;
    }


    /**
     * Returns the resource path to Assetsources icon
     *
     * @return string
     */
    public function getIconUri(): string
    {
        return $this->resourceManager->getPublicPackageResourceUriByPath($this->assetSourceOptions['icon']);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('Nextcloud asset source. Connected to %s', $this->assetSourceOptions['server']['baseUri']);
    }
}
