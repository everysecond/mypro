<?php


/**
 * ISTM
 *
 * User: Wudi<wudi@51idc.com>
 * Date: 16/5/4 17:21
 */
namespace Itsm\Model;

/**
 * Class Model
 *
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null find() find($id, $columns = ['*'])  Find a model by its primary key.
 * @method static \Illuminate\Database\Eloquent\Collection findMany() findMany($ids, $columns = ['*'])  Find a model by its primary key.
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection findOrFail() findOrFail($id, $columns = ['*'])  Find a model by its primary key or throw an exception.
 * @method static \Illuminate\Database\Eloquent\Model|static|null first() first($columns = ['*'])  Execute the query and get the first result.
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrFail() firstOrFail($columns = ['*'])  Execute the query and get the first result or throw an exception.
 * @method static \Illuminate\Database\Eloquent\Collection|static[] get() get($columns = ['*'])  Execute the query as a "select" statement.
 * @method static mixed value() value($column)  Get a single column's value from the first result of a query.
 * @method static mixed pluck() pluck($column)  Get a single column's value from the first result of a query.
 * @method static void chunk() chunk($count, callable $callback)  Chunk the results of the query.
 * @method static \Illuminate\Support\Collection lists() lists($column, $key = null)  Get an array with the values of a given column.
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate() paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)  Paginate the given query.
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate() simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page')  Paginate the given query into a simple paginator.
 * @method int update() update(array $values)  Update a record in the database.
 * @method int increment() increment($column, $amount = 1, array $extra = [])  Increment a column's value by a given amount.
 * @method int decrement() decrement($column, $amount = 1, array $extra = [])  Decrement a column's value by a given amount.
 * @method mixed delete() delete()  Delete a record from the database.
 * @method mixed forceDelete() forceDelete()  Run the default delete function on the builder.
 * @method static void onDelete() onDelete(\Closure $callback)  Register a replacement for the default delete function.
 * @method static \Illuminate\Database\Eloquent\Model[] getModels() getModels($columns = ['*'])  Get the hydrated models without eager loading.
 * @method static array eagerLoadRelations() eagerLoadRelations(array $models)  Eager load the relationships for the models.
 * @method \Illuminate\Database\Eloquent\Relations\Relation getRelation() getRelation($relation)  Get the relation instance for the given relation name.
 * @method static $this where() where($column, $operator = null, $value = null, $boolean = 'and')  Add a basic where clause to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhere() orWhere($column, $operator = null, $value = null)  Add an "or where" clause to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static has() has($relation, $operator = '>=', $count = 1, $boolean = 'and', \Closure $callback = null)  Add a relationship count condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static doesntHave() doesntHave($relation, $boolean = 'and', \Closure $callback = null)  Add a relationship count condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHas() whereHas($relation, \Closure $callback, $operator = '>=', $count = 1)  Add a relationship count condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDoesntHave() whereDoesntHave($relation, \Closure $callback = null)  Add a relationship count condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static orHas() orHas($relation, $operator = '>=', $count = 1)  Add a relationship count condition to the query with an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereHas() orWhereHas($relation, \Closure $callback, $operator = '>=', $count = 1)  Add a relationship count condition to the query with where clauses and an "or".
 * @method static $this with() with($relations)  Set the relationships that should be eager loaded.
 * @method static \Illuminate\Database\Query\Builder|static getQuery() getQuery()  Get the underlying query builder instance.
 * @method static $this setQuery() setQuery($query)  Set the underlying query builder instance.
 * @method static array getEagerLoads() getEagerLoads()  Get the relationships being eagerly loaded.
 * @method static $this setEagerLoads() setEagerLoads(array $eagerLoad)  Set the relationships being eagerly loaded.
 * @method static \Illuminate\Database\Eloquent\Model getModel() getModel()  Get the model instance being queried.
 * @method static $this setModel() setModel(Model $model)  Set a model instance for the model being queried.
 * @method static void macro() macro($name, \Closure $callback)  Extend the builder with a given callback.
 * @method static \Closure getMacro() getMacro($name)  Get the given macro by name.
 *
 *
 * @method static $this select() select($columns = ['*'])  Set the columns to be selected.
 * @method static \Illuminate\Database\Query\Builder|static selectRaw() selectRaw($expression, array $bindings = [])  Add a new "raw" select expression to the query.
 * @method static \Illuminate\Database\Query\Builder|static selectSub() selectSub($query, $as)  Add a subselect expression to the query.
 * @method static $this addSelect() addSelect($column)  Add a new select column to the query.
 * @method static $this distinct() distinct()  Force the query to only return distinct results.
 * @method static $this from() from($table)  Set the table which the query is targeting.
 * @method static $this join() join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)  Add a join clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static joinWhere() joinWhere($table, $one, $operator, $two, $type = 'inner')  Add a "join where" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static leftJoin() leftJoin($table, $first, $operator = null, $second = null)  Add a left join to the query.
 * @method static \Illuminate\Database\Query\Builder|static leftJoinWhere() leftJoinWhere($table, $one, $operator, $two)  Add a "join where" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static rightJoin() rightJoin($table, $first, $operator = null, $second = null)  Add a right join to the query.
 * @method static \Illuminate\Database\Query\Builder|static rightJoinWhere() rightJoinWhere($table, $one, $operator, $two)  Add a "right join where" clause to the query.
 * @method static $this whereRaw() whereRaw($sql, array $bindings = [], $boolean = 'and')  Add a raw where clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereRaw() orWhereRaw($sql, array $bindings = [])  Add a raw or where clause to the query.
 * @method static $this whereBetween() whereBetween($column, array $values, $boolean = 'and', $not = false)  Add a where between statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereBetween() orWhereBetween($column, array $values)  Add an or where between statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereNotBetween() whereNotBetween($column, array $values, $boolean = 'and')  Add a where not between statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereNotBetween() orWhereNotBetween($column, array $values)  Add an or where not between statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereNested() whereNested(\Closure $callback, $boolean = 'and')  Add a nested where statement to the query.
 * @method static $this addNestedWhereQuery() addNestedWhereQuery($query, $boolean = 'and')  Add another query builder as a nested where to the query builder.
 * @method static $this whereExists() whereExists(\Closure $callback, $boolean = 'and', $not = false)  Add an exists clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereExists() orWhereExists(\Closure $callback, $not = false)  Add an or exists clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereNotExists() whereNotExists(\Closure $callback, $boolean = 'and')  Add a where not exists clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereNotExists() orWhereNotExists(\Closure $callback)  Add a where not exists clause to the query.
 * @method static $this whereIn() whereIn($column, $values, $boolean = 'and', $not = false)  Add a "where in" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereIn() orWhereIn($column, $values)  Add an "or where in" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereNotIn() whereNotIn($column, $values, $boolean = 'and')  Add a "where not in" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereNotIn() orWhereNotIn($column, $values)  Add an "or where not in" clause to the query.
 * @method static $this whereNull() whereNull($column, $boolean = 'and', $not = false)  Add a "where null" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereNull() orWhereNull($column)  Add an "or where null" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereNotNull() whereNotNull($column, $boolean = 'and')  Add a "where not null" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orWhereNotNull() orWhereNotNull($column)  Add an "or where not null" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereDate() whereDate($column, $operator, $value, $boolean = 'and')  Add a "where date" statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereDay() whereDay($column, $operator, $value, $boolean = 'and')  Add a "where day" statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereMonth() whereMonth($column, $operator, $value, $boolean = 'and')  Add a "where month" statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static whereYear() whereYear($column, $operator, $value, $boolean = 'and')  Add a "where year" statement to the query.
 * @method static $this dynamicWhere() dynamicWhere($method, $parameters)  Handles dynamic "where" clauses to the query.
 * @method static $this groupBy() groupBy()  Add a "group by" clause to the query.
 * @method static $this having() having($column, $operator = null, $value = null, $boolean = 'and')  Add a "having" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orHaving() orHaving($column, $operator = null, $value = null)  Add a "or having" clause to the query.
 * @method static $this havingRaw() havingRaw($sql, array $bindings = [], $boolean = 'and')  Add a raw having clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static orHavingRaw() orHavingRaw($sql, array $bindings = [])  Add a raw or having clause to the query.
 * @method static $this orderBy() orderBy($column, $direction = 'asc')  Add an "order by" clause to the query.
 * @method static \Illuminate\Database\Query\Builder|static latest() latest($column = 'created_at')  Add an "order by" clause for a timestamp to the query.
 * @method static \Illuminate\Database\Query\Builder|static oldest() oldest($column = 'created_at')  Add an "order by" clause for a timestamp to the query.
 * @method static $this orderByRaw() orderByRaw($sql, $bindings = [])  Add a raw "order by" clause to the query.
 * @method static $this offset() offset($value)  Set the "offset" value of the query.
 * @method static \Illuminate\Database\Query\Builder|static skip() skip($value)  Alias to set the "offset" value of the query.
 * @method static $this limit() limit($value)  Set the "limit" value of the query.
 * @method static \Illuminate\Database\Query\Builder|static take() take($value)  Alias to set the "limit" value of the query.
 * @method static \Illuminate\Database\Query\Builder|static forPage() forPage($page, $perPage = 15)  Set the limit and offset for a given page.
 * @method static \Illuminate\Database\Query\Builder|static union() union($query, $all = false)  Add a union statement to the query.
 * @method static \Illuminate\Database\Query\Builder|static unionAll() unionAll($query)  Add a union all statement to the query.
 * @method static $this lock() lock($value = true)  Lock the selected rows in the table.
 * @method static \Illuminate\Database\Query\Builder lockForUpdate() lockForUpdate()  Lock the selected rows in the table for updating.
 * @method static \Illuminate\Database\Query\Builder sharedLock() sharedLock()  Share lock the selected rows in the table.
 * @method static string toSql() toSql()  Get the SQL representation of the query.
 * @method static array|static[] getFresh() getFresh($columns = ['*'])  Execute the query as a fresh "select" statement.
 * @method static int getCountForPagination() getCountForPagination($columns = ['*'])  Get the count of the total records for the paginator.
 * @method static string implode() implode($column, $glue = null)  Concatenate values of a given column as a string.
 * @method static bool exists() exists()  Determine if any rows exist for the current query.
 * @method static int count() count($columns = '*')  Retrieve the "count" result of the query.
 * @method static float|int min() min($column)  Retrieve the minimum value of a given column.
 * @method static float|int max() max($column)  Retrieve the maximum value of a given column.
 * @method static float|int sum() sum($column)  Retrieve the sum of the values of a given column.
 * @method static float|int avg() avg($column)  Retrieve the average of the values of a given column.
 * @method static float|int aggregate() aggregate($function, $columns = ['*'])  Execute an aggregate function on the database.
 * @method static bool insert() insert(array $values)  Insert a new record into the database.
 * @method static int insertGetId() insertGetId(array $values, $sequence = null)  Insert a new record and get the value of the primary key.
 * @method static void truncate() truncate()  Run a truncate statement on the table.
 * @method static void mergeWheres() mergeWheres($wheres, $bindings)  Merge an array of where clauses and bindings.
 * @method static \Illuminate\Database\Query\Expression raw() raw($value)  Create a raw database expression.
 * @method static array getBindings() getBindings()  Get the current query value bindings in a flattened array.
 * @method static array getRawBindings() getRawBindings()  Get the raw array of bindings.
 * @method static $this setBindings() setBindings(array $bindings, $type = 'where')  Set the bindings on the query builder.
 * @method static $this addBinding() addBinding($value, $type = 'where')  Add a binding to the query.
 * @method static $this mergeBindings() mergeBindings(\Illuminate\Database\Query\Builder $query)  Merge an array of bindings into our bindings.
 * @method static \Illuminate\Database\Query\Processors\Processor getProcessor() getProcessor()  Get the database query processor instance.
 * @method static \Illuminate\Database\Query\Grammars\Grammar getGrammar() getGrammar()  Get the query grammar instance.
 * @method static $this useWritePdo() useWritePdo()  Use the write pdo for query.
 *
 * @package App\Model
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    const DISABLED_YES = 1;
    const DISABLED_NO  = 0;
}