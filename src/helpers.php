<?php

if (! function_exists('check_duplicates')) {
    /**
     * @param string   $model
     * @param array    $search
     * @param array    $concat_search_columns
     * @param array    $priority_columns
     * @param int      $limit
     * @param int|null $with_similarity_min_common
     *
     * @return array
     */
    function check_duplicates(
        string $model,
        array $search,
        array $concat_search_columns = [],
        array $priority_columns = [],
        int $limit = 0,
        ?int $with_similarity_min_common = null
    ): array
    {
        try {

            $app = app('test-levenshtein')
                ->setModel($model)
                ->search($search)
                ->setConcatSearchColumns($concat_search_columns)
                ->setPriorityColumns($priority_columns);

            if ($limit > 0) {
                $app->setLimit($limit);
            }

            if ($with_similarity_min_common) {
                $app->withSimilarityMinCommon($with_similarity_min_common);
            }

            $items = $app
                ->checkDuplicates();

            return [
                'success' => true,
                'items'   => $items,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'items'   => [],
            ];
        }

    }
}

if (! function_exists('full_sql_from_query')) {
    function full_sql_from_query($query): string
    {
        $bindings = $query->getBindings();

        $bindings = collect($bindings)->map(function ($item, $key) {
            $item = str_replace('\\', '\\\\', $item);

            return $item;
        })->toArray();

        $sql = \Illuminate\Support\Str::replaceArray('?', $bindings, str_replace('?', "'?'", $query->toSql()));

        return $sql;
    }
}
