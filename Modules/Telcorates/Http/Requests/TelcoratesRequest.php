<?php

namespace Modules\Telcorates\Http\Requests;

use App\Models\EntityModel;
use App\Http\Requests\EntityRequest;
use Input;
use Utils;

class TelcoratesRequest extends EntityRequest
{
    protected $entityType = 'telcorates';

    public function entity()
    {
        if ($this->entity) {
            return $this->entity;
        }

        $class = EntityModel::getClassName($this->entityType);

        // The entity id can appear as invoices, invoice_id, public_id or id
        $publicId = false;
        $field = 'telcorate';

        if (! empty($this->$field)) {
            $publicId = $this->$field;
        }
        if (! $publicId) {
            $field = Utils::pluralizeEntityType($this->entityType);

            if (! empty($this->$field)) {
                $publicId = $this->$field;
            }
        }

        if (! $publicId) {
            $publicId = Input::get('public_id') ?: Input::get('id');
        }

        if (! $publicId) {
            return null;
        }

        if (method_exists($class, 'trashed')) {
            $this->entity = $class::scope($publicId)->withTrashed()->firstOrFail();
        } else {
            $this->entity = $class::scope($publicId)->firstOrFail();
        }

        return $this->entity;
    }     
}
