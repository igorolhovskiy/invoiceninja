<?php

namespace Modules\ImportColt\Transformers;

use Modules\Importcolt\Models\Importcolt;
use App\Ninja\Transformers\EntityTransformer;

/**
 * @SWG\Definition(definition="Importcolt", @SWG\Xml(name="Importcolt"))
 */

class ImportcoltTransformer extends EntityTransformer
{
    /**
    * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
    * @SWG\Property(property="user_id", type="integer", example=1)
    * @SWG\Property(property="account_key", type="string", example="123456")
    * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
    * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
    */

    /**
     * @param Importcolt $importcolt
     * @return array
     */
    public function transform(Importcolt $importcolt)
    {
        return array_merge($this->getDefaults($importcolt), [
            'name' => $importcolt->name,
            'id' => (int) $importcolt->public_id,
            'updated_at' => $this->getTimestamp($importcolt->updated_at),
            'archived_at' => $this->getTimestamp($importcolt->deleted_at),
        ]);
    }
}
