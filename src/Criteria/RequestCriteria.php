<?php namespace Znck\Repositories\Criteria;

use Illuminate\Http\Request;
use Znck\Repositories\Contracts\CriteriaInterface;
use Znck\Repositories\Contracts\RepositoryInterface;

class RequestCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * RequestCriteria constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the criteria on the repository.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $model
     * @param \Znck\Repositories\Contracts\RepositoryInterface $repository
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->applySearchFilter($repository, $model);
        $this->applyOrderFilter($model);
        $this->applyEagerLoadingFilter($model);

        return $model;
    }

    /**
     * @param \Som\Repositories\Repository $repository
     * @param \Illuminate\Database\Query\Builder|\Som\Eloquent\ValidatedModel $model
     */
    protected function applySearchFilter($repository, $model)
    {
        $allowed = $repository->getSearchableFields();
        $allowed = array_flip($allowed);
        $queries = $this->request->get('find', null);
        if (empty($allowed) or empty($queries) or ! (is_string($queries) or is_array($queries))) {
            return;
        }
        $queries = is_string($queries) ? explode('|', $queries) : $queries;
        $queries = array_map(function ($item) {
            return is_string($item) ? explode(',', $item) : $item;
        }, $queries);
        $allAllowed = array_has($allowed, '*');
        $model->where(function ($model) use ($queries, $allowed, $allAllowed) {
            foreach ($queries as $query) {
                if (is_array($query) and count($query) <= 4) {
                    if ($allAllowed or array_has($allowed, $query[0])) {
                        call_user_func_array([$model, 'where'], $query);
                    } else {
                        throw new \InvalidArgumentException('Invalid search query.');
                    }
                } else {
                    throw new \InvalidArgumentException('Invalid search query.');
                }
            }
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     *
     * @return void
     */
    protected function applyEagerLoadingFilter($model)
    {
        $with = $this->request->get('with', null);

        if (! empty($with) and (is_string($with) or is_array($with))) {
            $with = is_string($with) ? explode(',', $with) : $with;
            $with = array_map(function ($item) {
                return Str::camel($item);
            }, $with);
            $model->with($with);
        }
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function applyOrderFilter($model)
    {
        $orders = $this->request->get('sort', null);

        if (empty($orders) or ! (is_string($orders) or is_object($orders))) {
            return;
        }

        $orders = is_string($orders) ? explode(',', $orders) : $orders;
        foreach ($orders as $order) {
            $order = explode(':', $order);
            if (count($order) < 2) {
                $order[] = 'ASC';
            }

            $order[1] = Str::equals('DESC', Str::upper($order[1])) ? 'DESC' : 'ASC';
            $model->orderBy($order[0], $order[1]);
        }
    }
}
