<?php

namespace GetCodeDev\TestLevenshtein;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestLevenshtein
{
    protected ?Builder $query = null;

    protected $model = null;

    /**
     * How many results need to show
     *
     * @var int
     */
    protected int $limit = 5;

    /**
     * If need to add "having" AVG similarity for all searched columns
     *
     * @var bool
     */
    protected bool $use_similarity_min_common = false;

    /**
     * Min similarity percentage for all searched columns
     *
     * @var int
     */
    protected int $similarity_min_common = 50;


    /**
     * Columns and their values to search
     *
     * @var array
     */
    protected array $search = [];

    /**
     * Technically it's order by columns.
     * First column has the highest priority.
     *
     * @var array
     */
    protected array $priority_columns = [];

    /**
     * Default config for concat search columns.
     *
     * @var array|\string[][]
     */
    protected array $concat_search_columns = [
        'users' => [
            'full_name' => 'users.first_name users.last_name',
        ],

        'home' => [
            'address' => 'home.street, home.city, home.state home.zip'
        ],

        'jobs' => [
            'address' => 'jobs.street, jobs.city, jobs.state jobs.zip'
        ],
    ];


    public function checkDuplicates(): array
    {
        if (!$this->query) {
            throw new \Exception('Model class not exists.');
        }

        if (!$this->tableFromModelExists()) {
            throw new \Exception("Table '{$this->query->from}' not exists");
        }

        $this->prepareQuery();
        $this->prepareQueryOrderByAndHaving();
        $this->prepareQueryLimit();

        dd(
            $this->getSearch(),
            $this->getConcatSearchColumns(),
            $this->getPriorityColumns(),
            $this->needUsingSimilarityMinCommon(),
            full_sql_from_query($this->query)
        );

        return $this->query->get()->toArray();
    }

    protected function prepareQuery(): void
    {
        $table                = $this->query->from;
        $relation             = $table;

        $data = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->getSearch()), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($data as $key => $value) {
            if (is_array($value) && !$this->isRelationExists($key)) {
                continue;
            }

            if (is_array($value) && $this->isRelationExists($key)) {
                $table = $this->getRelationTableName($key);
                $relation = $key;

                $this->query->joinRelationship($key);
                continue;
            }

            $concat_search_column = $this->getConcatSearchColumn($relation . '.' . $key);

            if (!Schema::hasColumn($table, $key) && !$concat_search_column) {
                continue;
            }

            if ($concat_search_column) {
                $concat = $this->prepareAsConcatColumn($concat_search_column, $table, $key);
                $concat_similarity = $this->prepareAsSimilarityColumn($concat_search_column, $value, $table, $key);

                $this->query->addSelect($concat);
                $this->query->addSelect($concat_similarity);

            } else {
                $this->query->addSelect("{$table}.{$key} as {$table}_{$key}");

                $this->query->addSelect(
                    DB::raw("LEVENSHTEIN_RATIO({$table}.{$key}, '{$value}') as sim_{$table}_{$key}")
                );

            }
        }
    }

    protected function prepareQueryOrderByAndHaving(): void
    {
        $priority_columns = $this->getPriorityColumns();
        $order_by_columns = [];
        $sim_columns      = [];

        $table_model = $this->model->getTable();

        foreach ($priority_columns as $key => $column_full) {
            if (!str_contains($column_full, '.')) {
                $column_full = $table_model . '_' . $column_full;
            } else {
                $column_full = str_replace('.', '_', $column_full);
            }

            $order_by_columns[] = $column_full;
        }

        if (empty($order_by_columns)) {
            return;
        }

        foreach ($order_by_columns as $column) {
            if (!$this->isQuerySelectColumn($column)) {
                continue;
            }

            $this->query->orderByDesc('sim_' . $column);
            $sim_columns[] = 'sim_' . $column;
        }

        $this->prepareQueryMinSimilarityCommon($sim_columns);
    }

    protected function prepareQueryLimit(): void
    {
        $this->query->take($this->getLimit());
    }

    protected function prepareQueryMinSimilarityCommon(array $sim_columns = []): void
    {
        if (!$this->needUsingSimilarityMinCommon()) {
            return;
        }

        if (empty($sim_columns)) {
            return;
        }

        $sum_columns_string = implode(' + ', $sim_columns);
        $count_columns = count($sim_columns);

        $this->query->havingRaw("(($sum_columns_string) / $count_columns) >= ?", [$this->similarity_min_common]);
    }


    /**
     * @param string $check_column
     *
     * @return bool
     */
    protected function isQuerySelectColumn(string $check_column): bool
    {
        $selected_columns = $this->query->getQuery()->columns;

        foreach ($selected_columns as $column) {
            if ($column instanceof Expression) {
                $column = $column->getValue();
            }

            if (str_contains($column, "as sim_{$check_column}")) {
                return true;
            }
        }

        return false;
    }

    protected function prepareAsConcatColumn(string $concat_search_column, string $table, string $column): Expression
    {
        $concat = $this->getConcatColumn($concat_search_column);

        return DB::raw("CONCAT({$concat}) as {$table}_{$column}");
    }

    protected function prepareAsSimilarityColumn(string $concat_search_column, string $search_value, string $table, string $column): Expression
    {
        $concat = $this->getConcatColumn($concat_search_column);

        return DB::raw("LEVENSHTEIN_RATIO(CONCAT({$concat}), '{$search_value}') as sim_{$table}_{$column}");
    }

    protected function getConcatColumn(string $concat_search_column): string
    {
        $concat_array = preg_split("/([^a-zA-Z0-9._]+)/", $concat_search_column, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($concat_array as $key => $item) {
            if ($key % 2 !== 0) {
                $concat_array[$key] = "'" . $item . "'";
            } else {
                $concat_array[$key] = trim($item);
            }
        }

        return implode(', ', $concat_array);
    }

    protected function getConcatSearchColumn(string $column): ?string
    {
        return Arr::get($this->getConcatSearchColumns(), $column);
    }

    protected function isRelationExists(string $relation): bool
    {
        return method_exists($this->getModel(), $relation);
    }

    protected function getRelationTableName(string $relation): string
    {
        return $this->getModel()->{$relation}()->getRelated()->getTable();
    }


    /**
     * @param string $model
     *
     * @return $this
     */
    public function setModel(string $model): self
    {
        $this->model = (new $model());

        $table = $this->model->getTable();
        $this->query = $this->model->select("{$table}.id as {$table}_id");

        return $this;
    }


    /**
     * @return array|\string[][]
     */
    public function getConcatSearchColumns(): array
    {
        return $this->concat_search_columns;
    }


    /**
     * @return array
     */
    public function getSearch(): array
    {
        return $this->search;
    }


    /**
     * @param array $search
     *
     * @return $this
     */
    public function search(array $search): self
    {
        $this->search = $search;

        return $this;
    }

    protected function tableFromModelExists(): bool
    {
        return Schema::hasTable($this->query->from);
    }


    /**
     * @param array $concat_search_columns
     *
     * @return $this
     */
    public function setConcatSearchColumns(array $concat_search_columns): self
    {
        $this->concat_search_columns = array_merge($this->concat_search_columns, $concat_search_columns);

        return $this;
    }


    /**
     * @return array
     */
    public function getPriorityColumns(): array
    {
        /**
         * If priority columns are empty,
         * set search columns that are string (not relation array)
         */
        if (empty($this->getSearch())) {
            return collect($this->getSearch())
                ->filter(function($item, $key) {
                    return is_string($item);
                })
                ->keys()
                ->toArray();
        } else {
            return $this->priority_columns;
        }
    }


    /**
     * @param array $priority_columns
     *
     * @return $this
     */
    public function setPriorityColumns(array $priority_columns): self
    {
        $this->priority_columns = $priority_columns;

        return $this;
    }


    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }


    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }


    /**
     * @return null
     */
    public function getModel()
    {
        return $this->model;
    }


    /**
     * @return int
     */
    public function getSimilarityMin(): int
    {
        return $this->similarity_min;
    }


    /**
     * @param int $similarity_min
     *
     * @return $this
     */
    public function setSimilarityMin(int $similarity_min): self
    {
        $this->similarity_min = $similarity_min;

        return $this;
    }

    /**
     * @return bool
     */
    public function needUsingSimilarityMinCommon(): bool
    {
        return $this->use_similarity_min_common;
    }


    /**
     * @param int|null $similarity_min_common
     *
     * @return $this
     */
    public function withSimilarityMinCommon(?int $similarity_min_common = null): self
    {
        $this->use_similarity_min_common = true;

        if ($similarity_min_common) {
            $this->similarity_min_common = $similarity_min_common;
        }

        return $this;
    }
}
