<?php

namespace Modules\Telcopackages\Transformers;

use Modules\Telcopackages\Models\Telcopackages;
use App\Ninja\Transformers\EntityTransformer;

/**
 * @SWG\Definition(definition="Telcopackages", @SWG\Xml(name="Telcopackages"))
 */

class TelcopackagesTransformer extends EntityTransformer
{
    /**
    * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
    * @SWG\Property(property="user_id", type="integer", example=1)
    * @SWG\Property(property="account_key", type="string", example="123456")
    * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
    * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
    */

    /**
     * @param Telcopackages $telcopackages
     * @return array
     */
    public function transform(Telcopackages $telcopackages)
    {
        return array_merge($this->getDefaults($telcopackages), [
            'name' => $telcopackages->name,
            'amount_of_minutes' => $telcopackages->amount_of_minutes,
            'price' => $telcopackages->price,
            'id' => (int) $telcopackages->public_id,
            'updated_at' => $this->getTimestamp($telcopackages->updated_at),
            'archived_at' => $this->getTimestamp($telcopackages->deleted_at),
        ]);
    }
}
