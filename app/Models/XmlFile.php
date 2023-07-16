<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlFile extends Model
{
    protected $table = 'xml_files';

    protected $fillable = [
        'filename',
        'uploadDateTime',
        'lastCheckDateTime',
        'shop_name',
        'shop_link'
    ];

}
