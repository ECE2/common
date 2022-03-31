<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Rpc\Relations\HasOneForRpc;
use Ece2\Common\Model\Rpc\Relations\MorphToManyForRpc;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Str;

/**
 * 数据库 model 关联 rpc 数据.
 */
trait HasRelationshipsForRpc
{
    /**
     * Define a one-to-one relationship.
     * ("改" 写基类函数 自定义 hasOne).
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     */
    public function rpcHasOne($related, $foreignKey = null, $localKey = null): \Hyperf\Database\Model\Relations\HasOne
    {
        /** @var Model $instance */
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newRpcHasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Define a polymorphic many-to-many relationship.
     * ("改" 写基类函数 自定义 morphToMany).
     *
     * @param string $related
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param bool $inverse
     * @return \Hyperf\Database\Model\Relations\MorphToMany
     */
    public function rpcMorphToMany(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $inverse = false
    ) {
        $caller = $this->guessBelongsToManyRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name . '_id';

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        if (! $table) {
            $words = preg_split('/(_)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

            $lastWord = array_pop($words);

            $table = implode('', $words) . Str::plural($lastWord);
        }

        return $this->newRpcMorphToMany(
            $instance->newQuery(),
            $this,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $caller,
            $inverse
        );
    }

    /**
     * Instantiate a new HasOne relationship.
     *
     * @param string $foreignKey
     * @param string $localKey
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    protected function newRpcHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasOneForRpc($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Instantiate a new MorphToMany relationship.
     *
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relationName
     * @param bool $inverse
     * @return \Hyperf\Database\Model\Relations\MorphToMany
     */
    protected function newRpcMorphToMany(
        Builder $query,
        Model $parent,
        $name,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null,
        $inverse = false
    ) {
        return new MorphToManyForRpc(
            $query,
            $parent,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName,
            $inverse
        );
    }
}
