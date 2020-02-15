<?php

namespace Rupalipshinde\Template;

use Illuminate\Database\Eloquent\Model;

class TemplateModel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'secret',
    ];
    
     public function scopeSearch($query, $filter) {
        if ($filter != '') {
            return $query->where('title', 'like', '%' . $filter . '%');
        }
    }

   
}
