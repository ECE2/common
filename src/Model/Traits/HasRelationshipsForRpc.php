<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Rpc\Relations\BelongToForRpc;
use Ece2\Common\Model\Rpc\Relations\HasManyForRpc;
use Ece2\Common\Model\Rpc\Relations\HasOneForRpc;
use Ece2\Common\Model\Rpc\Relations\MorphManyForRpc;
use Ece2\Common\Model\Rpc\Relations\MorphToManyForRpc;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Stringable\Str;

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
     * Define a one-to-many relationship.
     * ("改" 写基类函数 自定义 hasMany).
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    public function rpcHasMany($related, $foreignKey = null, $localKey = null): \Hyperf\Database\Model\Relations\HasMany
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newRpcHasMany(
            $instance->newQuery(),
            $this,
            $instance->getTable() . '.' . $foreignKey,
            $localKey
        );
    }

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string|null  $type
     * @param  string|null  $id
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function rpcMorphMany($related, $name, $type = null, $id = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        // Here we will gather up the morph type and ID for the relationship so that we
        // can properly query the intermediate table of a relation. Finally, we will
        // get the table and create the relationship instances for the developers.
        [$type, $id] = $this->getMorphs($name, $type, $id);

        $table = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newRpcMorphMany($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
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
     * Define a many-to-many relationship.
     *
     * @param string $related
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relation
     * @return \Hyperf\Database\Model\Relations\BelongsToMany
     */
    public function rpcBelongsToMany(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ) {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related, $instance);
        }

        return $this->newRpcBelongsToMany(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function rpcBelongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        // If no foreign key was supplied, we can use a backtrace to guess the proper
        // foreign key name by using the name of the relationship function, which
        // when combined with an "_id" should conventionally match the columns.
        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation) . '_' . $instance->getKeyName();
        }

        // Once we have the foreign key names, we'll just create a new Model query
        // for the related models and returns the relationship instance which will
        // actually be responsible for retrieving and hydrating every relations.
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newRpcBelongsTo(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation
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
     * Instantiate a new HasMany relationship.
     *
     * @param string $foreignKey
     * @param string $localKey
     * @return \Hyperf\Database\Model\Relations\HasMany
     */
    protected function newRpcHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasManyForRpc($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Instantiate a new MorphMany relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $type
     * @param  string  $id
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    protected function newRpcMorphMany(Builder $query, Model $parent, $type, $id, $localKey)
    {
        return new MorphManyForRpc($query, $parent, $type, $id, $localKey);
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

    /**
     * Instantiate a new BelongsToMany relationship.
     *
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relationName
     * @return \Hyperf\Database\Model\Relations\BelongsToMany
     */
    protected function newRpcBelongsToMany(
        Builder $query,
        Model $parent,
                $table,
                $foreignPivotKey,
                $relatedPivotKey,
                $parentKey,
                $relatedKey,
                $relationName = null
    ) {
        return new BelongsToManyForRpc($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
    }

    /**
     * Instantiate a new BelongsTo relationship.
     *
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    protected function newRpcBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        return new BelongToForRpc($query, $child, $foreignKey, $ownerKey, $relation);
    }
}
