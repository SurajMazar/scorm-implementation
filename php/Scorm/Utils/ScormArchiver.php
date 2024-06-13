<?php

namespace App\Services\Manage\Scorm\Utils;

use App\Exceptions\Scrom\InvalidScormArchiveException;
use App\Services\Manage\Scorm\Enum\ScormConstant;
use App\Services\Manage\Scorm\Enum\ScormManifestVersions;
use App\Services\Manage\Scorm\Enum\ScormVersions;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScormArchiver
{
    /**
     * @var string
     */
    protected string $scormFilesystemDisk;
    /**
     * @var string
     */
    public string $scormRootDirectory;
    /**
     * @var Filesystem|Storage
     */
    public Filesystem|Storage $storage;

    /**
     * @param ScormLib $scormLib
     */
    public function __construct(protected ScormLib $scormLib)
    {
        $this->scormRootDirectory = $this->sanitizeUrl(config('scorm.scorm_root_directory'));
        $this->scormFilesystemDisk = config('scorm.scorm_filesystem_disk');
        $this->storage = Storage::disk($this->scormFilesystemDisk);
    }

    /**
     * @throws InvalidScormArchiveException
     */
    public function parseScormArchive(?UploadedFile $file): ?array
    {
        if (!$file) {
            return null;
        }
        $scormUuid = Str::uuid();
        $scormDom = $this->getScormManifestContent($file);
        $scos = $this->scormLib->parseOrganizationsNode($scormDom);

        /** FORMATTED PARSED CONTENT */
        return [
            'uuid'      => $scormUuid,
            'title'     => $this->getCourseTitle($scormDom),
            'file_path' => $this->generateFilePath($scormUuid),
            'version'   => $this->getScormVersion($scormDom),
            'entry_url' => data_get($scos, '0.entryUrl') ?? data_get($scos, '0.scoChildren.0.entryUrl'),
            'scos'      => $scos,
        ];
    }

    /**
     * @throws InvalidScormArchiveException
     */
    public function getScormManifestContent(UploadedFile $file): \DOMDocument
    {
        $contents = '';
        $zip = new \ZipArchive();
        $zip->open($file);
        /** READING THE MANIFEST FILE FROM THE ZIP*/
        $stream = $zip->getStream(ScormConstant::MANIFEST_FILE_NAME->value);
        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();
        if (!$dom->loadXML($contents)) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }
        $zip->close();

        return $dom;
    }

    /**
     * @throws InvalidScormArchiveException
     */
    public function getScormVersion(\DOMDocument $scormData): ?string
    {
        $element = $scormData->getElementsByTagName(ScormConstant::SCHEMA_VERSION_TAG->value);
        if (!$element->length > 0) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }
        $version = $element->item(0)->textContent;
        $version = match ($version) {
            ScormManifestVersions::SCORM_12->value => ScormVersions::SCORM_12->value,
            ScormManifestVersions::SCORM_2004_3RD_EDITION->value,
            ScormManifestVersions::CAM_1_3->value,
            ScormManifestVersions::SCORM_2004_4TH_EDITION->value => ScormVersions::SCORM_2004->value,
            default                                              => null,
        };
        if (!$version) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }

        return $version;
    }

    public function storeScormContent(string $filepath, UploadedFile $file)
    {
        try {
            $zip = new \ZipArchive();
            $zip->open($file);
            $zip->extractTo(sys_get_temp_dir().'/'.$filepath);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $f = sys_get_temp_dir().'/'.$filepath.'/'.$fileName;
                if (is_file($f) && !is_dir($f)) {
                    $this->storage->putFileAs($filepath, new File($f), $fileName);
                }
                $this->storage->delete($f);
            }
            $zip->close();

//            $this->storage->putFileAs($filepath, $file, 'scorm.zip');
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param \DOMDocument $scormData
     *
     * @return string
     */
    public function getCourseTitle(\DOMDocument $scormData): string
    {
        $element = $scormData->getElementsByTagName('title');

        return Str::of($element->item(0)->textContent)->trim('/n')->trim();
    }

    /**
     * @param string $hash
     *
     * @return string
     */
    public function generateFilePath(string $hash): string
    {
        return sprintf('%s/%s/', $this->scormRootDirectory, $hash);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function deleteScormFolder(string $path): bool
    {
        try {
            $directory = sprintf('/%s/%s/', $this->scormRootDirectory, $path);
            if ($this->storage->directoryExists($directory)) {
                return $this->storage->deleteDirectory($directory);
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function sanitizeUrl(string $url): string
    {
        if (Str::substr($url, -1) === '/') {
            return substr($url, 0, -1);
        }

        return $url;
    }
}
