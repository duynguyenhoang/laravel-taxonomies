<?php

namespace Bobo\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Taxonomy
 * @package Bobo\Taxonomies\Models
 */
class Taxonomy extends Model
{
    use SoftDeletes;

    /**
     * @todo make this editable via config file
     * @inheritdoc
     */
    protected $table = 'taxonomies';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'sort',
    ];

    /**
     * @inheritdoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent');
    }

    /**
     * @param  $query
     * @param  string $taxonomy
     * @return mixed
     */
    public function scopeTaxonomy($query, $taxonomy)
    {
        return $query->where('taxonomy', $taxonomy);
    }

    /**
     * @param  $query
     * @param  string $term
     * @param  string $taxonomy
     * @return mixed
     */
    public function scopeTerm($query, $term, $taxonomy = 'major')
    {
        return $query->whereHas('term', function ($q) use ($term, $taxonomy) {
            $q->where('name', $term);
        });
    }

    /**
     * @param  $query
     * @param  string $searchTerm
     * @param  string $taxonomy
     * @return mixed
     */
    public function scopeSearch($query, $searchTerm, $taxonomy = 'major')
    {
        return $query->whereHas('term', function ($q) use ($searchTerm, $taxonomy) {
            $q->where('name', 'like', '%' . $searchTerm . '%');
        });
    }
}
