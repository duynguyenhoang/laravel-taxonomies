<?php

namespace Bobo\Taxonomies\Traits;

use Illuminate\Database\Eloquent\Collection;
use Bobo\Taxonomies\Models\Taxable;
use Bobo\Taxonomies\Models\Taxonomy;
use Bobo\Taxonomies\Models\Term;
use Bobo\Taxonomies\TaxableUtils;

/**
 * Class HasTaxonomies
 * @package Bobo\Taxonomies\Traits
 */
trait HasTaxonomies
{
    /**
     * Return collection of taxonomies related to the taxed model
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function taxed()
    {
        return $this->morphMany(Taxable::class, 'taxable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function taxonomies()
    {
        return $this->morphToMany(Taxonomy::class, 'taxable');
    }

    /**
     * @param $terms
     * @param $taxonomy
     * @param int $parent
     * @param int $order
     */
    public function addTerm($terms, $taxonomy, $parent = 0, $order = 0)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        //@todo Return
        $this->createTaxables($terms, $taxonomy, $parent, $order);

        $taxonomies = Taxonomy::whereIn('term_id', $terms)->where('taxonomy', $taxonomy)->get();

        if (count($taxonomies) > 0) {
            foreach ($taxonomies as $taxonomy) {
                if ($this->taxonomies()->where('taxonomy_id', $taxonomy->id)->first())
                    continue;

                $this->taxonomies()->attach($taxonomy->id);
            }

            return;
        }

        $this->taxonomies()->detach();
    }

    /**
     * @param $taxonomyId
     */
    public function setCategory($taxonomyId)
    {
        $this->taxonomies()->attach($taxonomyId);
    }

    /**
     * @param $terms
     * @param $taxonomy
     * @param int $parent
     * @param int $order
     * @return array
     */
    public function createTaxables($terms, $taxonomy, $parent = 0, $order = 0)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        return TaxableUtils::createTaxonomies($terms, $taxonomy, $parent, $order);
    }

    /**
     * @param string $by
     * @return mixed
     */
    public function getTaxonomies($by = 'id')
    {
        return $this->taxonomies->pluck($by);
    }

    /**
     * @param string $taxonomy
     * @return array
     */
    public function getTermIds($taxonomy = '')
    {
        if ($taxonomy) {
            $termIds = $this->taxonomies->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $termIds = $this->getTaxonomies('term_id');
        }

        return $termIds;
    }

    /**
     * @param string $taxonomy
     * @return Collection
     */
    public function getTerms($taxonomy = '')
    {
        $termIds = $this->getTermIds($taxonomy);

        return Term::whereIn('id', $termIds)->get();
    }

    /**
     * @param $term
     * @param string $taxonomy
     * @return mixed
     */
    public function getTerm($term, $taxonomy = '')
    {
        if ($taxonomy) {
            $termIds = $this->taxonomies->where('term_id', $term)->where('taxonomy', $taxonomy)->pluck('term_id');
        } else {
            $termIds = $this->getTaxonomies('term_id');
        }

        return Term::whereIn('id', $termIds)->first();
    }

    /**
     * @param $term
     * @param string $taxonomy
     * @return bool
     */
    public function hasTerm($term, $taxonomy = '')
    {
        return (bool)$this->getTerm($term, $taxonomy);
    }

    /**
     * @param $term
     * @param string $taxonomy
     * @return mixed
     */
    public function removeTerm($term, $taxonomy = '')
    {
        if ($term = $this->getTerm($term, $taxonomy)) {
            if ($taxonomy) {
                $taxonomy = $this->taxonomies->where('taxonomy', $taxonomy)->where('term_id', $term->id)->first();
            } else {
                $taxonomy = $this->taxonomies->where('term_id', $term->id)->first();
            }

            return $this->taxed()->where('taxonomy_id', $taxonomy->id)->delete();
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function removeAllTerms()
    {
        return $this->taxed()->delete();
    }

    /**
     * Filter model to subset with the given tags
     *
     * @param object $query
     * @param array $terms
     * @param string $taxonomy
     * @return object $query
     */
    public function scopeWithTerms($query, $terms, $taxonomy)
    {
        $terms = TaxableUtils::makeTermsArray($terms);

        foreach ($terms as $term) {
            $this->scopeWithTerm($query, $term, $taxonomy);
        }

        return $query;
    }

    /**
     * Filter model to subset with the given tags
     *
     * @param object $query
     * @param string $term
     * @param string $taxonomy
     * @return
     */
    public function scopeWithTax($query, $term, $taxonomy)
    {
        $termIds = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');

        $term = Term::whereIn('id', $termIds)->where('name', '=', $term)->first();

        $taxonomy = Taxonomy::where('term_id', $term->id)->first();

        return $query->whereHas('taxed', function ($q) use ($term, $taxonomy) {
            $q->where('taxonomy_id', $taxonomy->id);
        });
    }

    /**
     * @param $query
     * @param $term
     * @param $taxonomy
     * @return mixed
     */
    public function scopeWithTerm($query, $term, $taxonomy)
    {
        $termIds = Taxonomy::where('taxonomy', $taxonomy)->pluck('term_id');

        $term = Term::whereIn('id', $termIds)->where('name', '=', $term)->first();

        $taxonomy = Taxonomy::where('term_id', $term->id)->first();

        return $query->whereHas('taxonomies', function ($q) use ($term, $taxonomy) {
            $q->where('term_id', $term->id);
        });
    }

    /**
     * @param $query
     * @param $taxonomyId
     * @return mixed
     */
    public function scopeHasCategory($query, $taxonomyId)
    {
        return $query->whereHas('taxed', function ($q) use ($taxonomyId) {
            $q->where('taxonomy_id', $taxonomyId);
        });
    }
}