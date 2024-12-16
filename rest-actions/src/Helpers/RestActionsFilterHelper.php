<?php

namespace Amondar\RestActions\Helpers;

use Amondar\Sextant\Library\Actions\Expand;
use Amondar\Sextant\Library\Actions\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Trait RestActionsFilterHelper
 *
 * @version 1.0.0
 * @date    05.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsFilterHelper
{

    /**
     * Default pagination limit, if limit in request empty.
     *
     * @var int
     */
    protected $DEFAULT_PAGINATE = 10;

    /**
     * Limit possible operations.
     * count - return counter of given filter conditions.
     * infinity - request from server maximum data as server can process in one response.
     *
     * @var array
     */
    protected $AVAILABLE_OPERATION_LIMITS = [ 'infinity', 'count' ];

    /**
     * Return ARRAY of items with transformation.
     *
     * @param $data
     *
     * @return array
     */
    public function getItems($data)
    {
        if ( $this->needTransformation() ) {
            return $this->restActionsTransformData($data);
        }

        return $data;
    }

    /**
     * Return item with transformation.
     *
     * @param $id
     * @param $request
     * @param $params
     *
     * @return Model|\Illuminate\Routing\Route|object|string
     */
    public function getItem($id, Request $request, $params = [])
    {
        // Return cache if enabled.
        if ( $this->restActionsCacheNeeded() ) {
            return $this->getRestActionsCache($request, $params, $id);
        }

        // Add filter section if $id is simple value.
        if ( $id !== NULL && ! is_object($id) ) {
            $params[ 'filter' ][ $this->restMakeModel()->getRouteKeyName() ] = $id;
        }

        if ( $id && $id instanceof Model ) {
            return $this->applySextantOperationsToExistRouteModel($id, $request, $params);
        } else {
            return $this->getBaseFilterQuery($request)
                        ->withSextant($request, $params, $this->action[ 'sextantRestrictions' ])
                        ->firstOrFail();
        }
    }

    /**
     * Apply sextant operations to exist route model.
     *
     * @param Model   $routeItem
     * @param Request $request
     * @param array   $params
     *
     * @return Model
     */
    public function applySextantOperationsToExistRouteModel(Model $routeItem, Request $request, $params = [])
    {
        $expands = [];
        $sortExpands = [];
        $sortExpandsRelations = [];
        if ( $request->has($requestExpandKey = app('sextant')->getConfigRequestKey('expand')) ) {
            $expands = ( new Expand($routeItem->newQuery(), $requestExpandKey, $request, $this->action[ 'sextantRestrictions' ], $params) )->expands->toArray();
        }

        if ( $request->has($requestSortExpandKey = app('sextant')->getConfigRequestKey('sortExpand')) ) {
            $sortExpands = ( new Sort($routeItem->newQuery(), $requestSortExpandKey, $request, $this->action[ 'sextantRestrictions' ], $params) )->getEagerLoadRelationsArray(collect([]));
            $sortExpandsRelations = array_keys($sortExpands);
        }

        return $routeItem->load(array_merge(array_diff($expands, $sortExpandsRelations), $sortExpands));
    }

    /**
     * Return base query builder.
     *
     * @param Request      $request
     *
     * @return Builder
     */
    protected function getBaseFilterQuery(Request $request)
    {
        return $this->restMakeModel()->newQuery();
    }

    /**
     * Paginate collection.
     *
     * @param Collection $data
     * @param Request    $request
     *
     * @return LengthAwarePaginator
     */
    public function paginateCollection(Collection $data, Request $request)
    {
        $page = $this->getPage($request);
        $limit = $this->getPaginate($request);

        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $data->slice(( $page - 1 ) * $limit, $limit)->values()->all();

        //Create our paginator and pass it to the view
        return new LengthAwarePaginator($currentPageSearchResults, $data->count(), $limit, $page);
    }

    /**
     * Return page value.
     *
     * @param Request|null $request
     *
     * @return int|mixed
     */
    public function getPage(Request $request = NULL)
    {
        return ( $request && $request->has('page') ) ? (int) $request->input('page') : 1;
    }

    /**
     * Return paginate value.
     *
     * @param Request|null $request
     *
     * @param array        $params
     *
     * @return int|mixed
     */
    public function getPaginate(Request $request = NULL, $params = [])
    {
        if ( $request && $request->has('limit') ) {
            $limit = $request->input('limit');
            $limit = in_array($limit, $this->AVAILABLE_OPERATION_LIMITS) ? $limit : (int) $limit;
        } elseif ( ! empty($params[ 'limit' ]) ) {
            $limit = (int) $params[ 'limit' ];
        } else {
            $limit = $this->DEFAULT_PAGINATE;
        }

        if ( (int) $limit > $this->action[ 'protectedLimit' ] ) {
            $limit = $this->action[ 'protectedLimit' ];
        }

        return $limit;
    }

    /**
     * Paginate request.
     *
     * @param Request $request
     * @param array   $params
     *
     * @return mixed
     */
    public function paginate(Request $request, array $params = [])
    {
        // Return cache if enabled.
        if ( $this->restActionsCacheNeeded() ) {
            return $this->getRestActionsCache($request, $params);
        }

        $limit = $this->getPaginate($request, $params);

        /** @var Builder $query */
        $query = $this->getBaseFilterQuery($request)->withSextant($request, $params, $this->action[ 'sextantRestrictions' ]);

        if ( method_exists($query->getModel(), 'scopeSortMongoRelations') ) {
            return $this->runMongoFilter($query, $limit);
        } else {
            return $this->runMysqlFilter($query, $limit);
        }
    }

    /**
     * Run mongo filter with sorting
     *
     * @param      $query
     * @param null $limit
     *
     * @return mixed
     * @since 1.1.9
     */
    public function runMongoFilter($query, $limit = NULL)
    {
        switch ( $limit ) {
            case 'infinity':
                return $query->limit($this->action[ 'protectedLimit' ])->sortMongoRelations();
                break;
            case 'count':
                return $query->count();
                break;
            default:
                return $query->sortMongoRelations($limit);
        }
    }

    /**
     * Run mySql filter with sorting
     *
     * @param $query
     * @param $limit
     *
     * @return mixed
     * @since 1.1.9
     */
    public function runMysqlFilter($query, $limit)
    {
        switch ( $limit ) {
            case 'infinity':
                return $query->limit($this->action[ 'protectedLimit' ])->get();
                break;
            case 'count':
                return $query->count();
                break;
            default:
                return $query->paginate($limit);
        }
    }

}