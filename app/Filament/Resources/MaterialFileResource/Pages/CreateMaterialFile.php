<?php

namespace App\Filament\Resources\MaterialFileResource\Pages;

use App\Filament\Resources\MaterialFileResource;
use App\Models\MaterialFile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMaterialFile extends CreateRecord
{
    protected static string $resource = MaterialFileResource::class;

    /**
     * When one or more files are uploaded, create a library entry per file.
     * (Links/videos fall through to a normal single create.)
     */
    protected function handleRecordCreation(array $data): Model
    {
        $files = array_filter((array) ($data['files'] ?? []));
        unset($data['files']);

        if (! empty($files)) {
            $folderId = $data['material_folder_id'] ?? null;
            $first = null;

            foreach ($files as $path) {
                $record = MaterialFile::create(MaterialFileResource::payloadForUpload($path, $folderId));
                $first ??= $record;
            }

            return $first;
        }

        return MaterialFile::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
