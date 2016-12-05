<?php

namespace Bobo\Taxonomies\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TermTranslation
 * @package Bobo\Taxonomies\Models
 */
class TermTranslation extends Model
{
	use Sluggable;

    public $timestamps = false;

    /**
     * @todo make this editable via config file
     * @inheritdoc
     */
    protected $fillable = [
        'name',
        'slug',
    ];

	/**
	 * Return the sluggable configuration array for this model.
	 *
	 * @return array
	 */
	public function sluggable()
	{
		return [
			'slug' => [
				'source' => 'name'
			]
		];
	}
}
