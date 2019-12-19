<?php

namespace Modules\ExportSepa\Transformers;

use Modules\Exportsepa\Models\Exportsepa;
use App\Ninja\Transformers\EntityTransformer;

/**
 * @SWG\Definition(definition="Exportsepa", @SWG\Xml(name="Exportsepa"))
 */

class ExportsepaTransformer extends EntityTransformer
{
    /**
    * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
    * @SWG\Property(property="user_id", type="integer", example=1)
    * @SWG\Property(property="account_key", type="string", example="123456")
    * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
    * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
    */

    /**
     * @param Exportsepa $exportsepa
     * @return array
     */
    public function transform(Exportsepa $exportsepa)
    {
        return array_merge($this->getDefaults($exportsepa), [
            
            'id' => (int) $exportsepa->public_id,
            'updated_at' => $this->getTimestamp($exportsepa->updated_at),
            'archived_at' => $this->getTimestamp($exportsepa->deleted_at),
        ]);
    }
}
