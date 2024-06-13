<?php

namespace App\Services\Manage\Scorm;

use App\Models\Scorm;
use App\Services\Manage\Scorm\Utils\ScormArchiver;
use App\Services\Public\Scorm\ScormUserTokenService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ScormService
{
    /**
     * @param ScormArchiver         $scormArchiver
     * @param ScormScoService       $scormScoService
     * @param ScormUserTokenService $scormUserTokenService
     */
    public function __construct(
        protected ScormArchiver $scormArchiver,
        protected ScormScoService $scormScoService,
        protected ScormUserTokenService $scormUserTokenService
    ) {
    }

    public function upload(string $modelType, int $modelId, UploadedFile $file, ?Scorm $existing = null): false|Scorm
    {
        DB::beginTransaction();

        try {
            $scormData = $this->scormArchiver->parseScormArchive($file);
            /**
             * REMOVES THE OLD SCORM FILE IF EXISTS.
             */
            if ($existing) {
                $deletePrevious = $this->delete($existing);
                if (!$deletePrevious) {
                    DB::rollBack();

                    return false;
                }
            }
            /**
             * @var Scorm $scorm
             */
            $scorm = Scorm::query()->create([
                'model_id'         => $modelId,
                'model_type'       => $modelType,
                'uuid'             => data_get($scormData, 'uuid'),
                'title'            => data_get($scormData, 'title'),
                'version'          => data_get($scormData, 'version'),
                'origin_file'      => $file->getClientOriginalName(),
                'origin_file_mime' => $file->getClientMimeType(),
                'entry_url'        => data_get($scormData, 'entry_url'),
            ]);
            /*** STORE SCORM SCOS */
            $content = $this->scormScoService->bulkStore($scorm, data_get($scormData, 'scos', []));
            $file = $this->scormArchiver->storeScormContent(data_get($scormData, 'file_path'), $file);
            if (!$file || !$content) {
                DB::rollBack();

                return false;
            }
        } catch (\Exception $exception) {
            DB::rollBack();

            return false;
        }
        DB::commit();

        return $scorm;
    }

    /**
     * @param Scorm $scorm
     *
     * @return bool
     */
    public function delete(Scorm $scorm): bool
    {
        DB::beginTransaction();

        try {
            $scorm->delete();
            $scormFolderDelete = $this->scormArchiver->deleteScormFolder($scorm->uuid);
            if (!$scormFolderDelete) {
                DB::rollBack();

                return false;
            }
            DB::commit();

            return true;
        } catch (\Exception $exception) {
            DB::rollBack();

            return false;
        }
    }
}
