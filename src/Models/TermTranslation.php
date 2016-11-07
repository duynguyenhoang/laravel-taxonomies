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
