<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of FreightTemplateDetails
 *
 * @author wangxh
 */
class FreightTemplateDetails extends Base {

    protected $table = 'nlsg_freight_template_details';

    public function d_list() {
        return $this->hasMany('App\Models\FreightTemplateDetailsList', 'd_id', 'id')
                        ->select(['area_id', 'd_id']);
    }

}
