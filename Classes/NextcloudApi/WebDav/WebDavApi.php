<?php
declare(strict_types=1);

namespace DL\AssetSource\Nextcloud\NextcloudApi\WebDav;

/*
 * This file is part of the DL.AssetSource.Nextcloud package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSource\Nextcloud\Exception\NextcloudAssetSourceException;
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\Dto\NextcloudAsset;
use DL\AssetSource\Nextcloud\NextcloudApi\WebDav\Dto\SearchResult;
use Neos\Utility\Files;
use Sabre\DAV\Client;

class WebDavApi
{
    public const ENDPOINT_URL_PART = '/remote.php/dav/files/';

    /**
     * @var string
     */
    private $userName;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $assetResultLimit;

    /**
     * @var string[]
     */
    private $properties = [
        '{DAV:}getlastmodified' => 'lastModified',
        '{DAV:}getcontenttype' => 'contentType',
        '{DAV:}getcontentlength' => 'contentLength',
        '{DAV:}is-collection' => 'isCollection',
        '{http://Nextcloud.org/ns}has-preview' => 'hasPreview',
        '{http://owncloud.org/ns}fileid' => 'fileId',
    ];

    public function __construct(array $settings)
    {
        $settings['server']['baseUri'] = Files::concatenatePaths([$settings['server']['baseUri'], 'remote.php/dav']);

        $this->userName = $settings['server']['userName'];
        $this->client = new Client($settings['server']);

        $this->assetResultLimit = $settings['assetResultLimit'] ?? 200;
    }

    /**
     * @param string $path
     * @return NextcloudAsset
     * @throws \Sabre\HTTP\ClientHttpException
     * @throws NextcloudAssetSourceException
     */
    public function propfind(string $path): NextcloudAsset
    {
        $clarkResult = $this->client->propFind($path, array_keys($this->properties));

        if (empty($clarkResult)) {
            throw new NextcloudAssetSourceException(sprintf('Asset at path "%s" does not exist', $path), 1551705470);
        }

        return new NextcloudAsset($path, $clarkResult);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getFileContent(string $path): string
    {
        $fileInfo =  $this->client->request('GET', $path);
        return $fileInfo['body'];
    }

    /**
     * @param string $searchString
     * @param int $limit
     * @param int $offset
     * @return SearchResult
     */
    public function search(string $searchString, int $limit, int $offset = 0): SearchResult
    {
        $requestBody = $this->buildRequestBody($this->userName, $searchString, $this->assetResultLimit);

        $response = $this->client->request('SEARCH', '', $requestBody, ['Content-Type' => 'text/xml']);

        $offset = $offset > $this->assetResultLimit ? $this->assetResultLimit : $offset;
        $multiStatus = $this->client->parseMultiStatus($response['body']);
        $resultCount = count($multiStatus);
        $results = $this->parseResponseToAssetList(array_slice($multiStatus, $offset, $limit));

        return new SearchResult($results, $resultCount);
    }

    /**
     * @param array $multiStatus
     * @return array
     */
    private function parseResponseToAssetList(array $multiStatus): array
    {
        $assets = [];

        foreach ($multiStatus as $filePath => $status) {

            if ($this->isDirectory($status[200])) {
                continue;
            }

            if (!isset($status[200])) {
                continue;
            }

            $assets[$filePath] = new NextcloudAsset($filePath, $status[200]);
        }

        return $assets;
    }

    private function buildRequestBody(string $userName, string $search, int $limit)
    {
        $search = $search === '' ? '%' : '%' . $search . '%';

        return sprintf('<?xml version="1.0"?>
            <d:searchrequest xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://Nextcloud.org/ns">
                <d:basicsearch>
                    <d:select>
                        <d:prop>
                            <d:getlastmodified/>
                            <d:getcontenttype/>
                            <d:getcontentlength/>
                            <d:getlastmodified/>
                            <nc:has-preview/>
                            <oc:fileid/>
                        </d:prop>
                    </d:select>
                    <d:from>
                        <d:scope>
                            <d:href>/files/%s</d:href>
                            <d:depth>infinity</d:depth>
                        </d:scope>
                    </d:from>
                    <d:where>
                        <d:like>
                            <d:prop>
                                <d:displayname/>
                            </d:prop>
                            <d:literal>%s</d:literal>
                        </d:like>
                    </d:where>
                    <d:limit>
                        <d:nresults>%s</d:nresults>
                    </d:limit>
                </d:basicsearch>
            </d:searchrequest>',
            $userName, $search, $limit
        );
    }

    /**
     * @param array $object
     * @return bool
     */
    private function isDirectory(array $object): bool
    {
        return !isset($object['{DAV:}getcontenttype']) || $object['{DAV:}getcontenttype'] === '';
    }

}
