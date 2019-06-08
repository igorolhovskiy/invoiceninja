<?php

namespace Modules\Telcorates\Transformers;

use Modules\Telcorates\Models\Telcorates;
use App\Ninja\Transformers\EntityTransformer;

/**
 * @SWG\Definition(definition="Telcorates", @SWG\Xml(name="Telcorates"))
 */

class TelcoratesTransformer extends EntityTransformer
{
    /**
    * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
    * @SWG\Property(property="user_id", type="integer", example=1)
    * @SWG\Property(property="account_key", type="string", example="123456")
    * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
    * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
    */

    /**
     * @param Telcorates $telcorates
     * @return array
     */
    public function transform(Telcorates $telcorates)
    {
        return array_merge($this->getDefaults($telcorates), [
            'name' => $telcorates->name,
            'id' => (int) $telcorates->public_id,
            'updated_at' => $this->getTimestamp($telcorates->updated_at),
            'archived_at' => $this->getTimestamp($telcorates->deleted_at),
        ]);
    }
}
