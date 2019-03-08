<?php
declare(strict_types=1);

namespace DL\AssetSource\NextCloud\NextCloudApi\WebDav\Dto;

/*
 *  (c) 2019 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

class NextCloudAsset
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $fileId;

    /**
     * @var \DateTime
     */
    protected $lastModified;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var int
     */
    protected $contentLength;

    /**
     * @var bool
     */
    protected $hasPreview;

    public function __construct(string $path, array $clarkResult)
    {
        $this->path = $path;
        $this->fileId = (int)($clarkResult['{http://owncloud.org/ns}fileid'] ?? 0);
        $this->lastModified = \DateTime::createFromFormat(DATE_RFC1123, $clarkResult['{DAV:}getlastmodified']);
        $this->contentType = $clarkResult['{DAV:}getcontenttype'] ?? '?/?';
        $this->contentLength = (int)($clarkResult['{DAV:}getcontentlength'] ?? 0);
        $this->hasPreview = isset($clarkResult['{http://nextcloud.org/ns}has-preview']) && $clarkResult['{http://nextcloud.org/ns}has-preview'] === 'true';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return pathinfo(urldecode($this->path), PATHINFO_BASENAME);
    }

    /**
     * @return int
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified(): \DateTime
    {
        return $this->lastModified;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return int
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    /**
     * @return bool
     */
    public function isHasPreview(): bool
    {
        return $this->hasPreview;
    }
}
