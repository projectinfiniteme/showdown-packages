<?php

namespace AttractCores\LaravelCoreClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class CoreRepository
{

    /**
     *
     * @var Model $model
     */
    protected Model $model;

    /**
     * Repository constructor.
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Make model instance.
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = app($this->model());

        if ( ! $model instanceof Model ) {
            abort(500, "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public abstract function model();

    /**
     * If method does not exists - call model function.
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $result = call_user_func_array([ $this->model, $method ], $args);

        if($result === NULL){
            return $this->model;
        }

        return $result;
    }

    /**
     * Return model instance.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set model instance
     *
     * @param Model $model
     *
     * @return Model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this->model;
    }

    /**
     * Set model instance
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setThisModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Create and return new model instance.
     *
     * @param array $data
     *
     * @return $this
     */
    public function createThisModel(array $data)
    {
        $this->createModel($data);

        return $this;
    }

    /**
     * Create and return new model instance.
     *
     * @param array $data
     *
     * @return Model
     */
    public function createModel(array $data)
    {
        $this->model = new $this->model($data);

        return $this->model;
    }

    /**
     * Update model instance.
     *
     * @param        $data
     * @param ?Model $model
     *
     * @return $this
     */
    public function updateThisModel($data, $model = NULL)
    {
        $this->updateModel($data, $model);

        return $this;
    }

    /**
     * Update model instance.
     *
     * @param        $data
     * @param ?Model $model
     *
     * @return Model
     */
    public function updateModel($data, $model = NULL)
    {
        if ( ! is_null($model) ) {
            $this->model = $model;
        }
        $this->model->update($data);

        return $this->model;
    }

    /**
     * Get by condition.
     *
     * @param      $column
     * @param      $value
     *
     * @param bool $isFirst
     *
     * @return Model
     */
    public function getBy($column, $value, $isFirst = true)
    {
        if ( $isFirst ) {
            return $this->where($column, $value)->first();
        }

        return $this->where($column, $value)->get();
    }

    /**
     * Delete by condition.
     *
     * @param $column
     * @param $value
     *
     * @return $this
     */
    public function deleteBy($column, $value)
    {
        if ( is_array($value) ) {
            $models = $this->whereIn($column, $value)->get();
            $models->each(function ($model) {
                $this->delete($model);
            });

            return $this;
        } else {
            $this->where($column, '=', $value)->first()->delete();

            return $this;
        }
    }

    /**
     * Delete model instance.
     *
     * @return mixed
     * @throws \Exception
     */
    public function delete()
    {
        return $this->model->delete();
    }

    /**
     * Run saving hook. Dirty fields should be available.
     * Can't be used as public, cuz changes applied without DB saving.
     *
     * @param Request $request
     * @param array   $validated
     *
     * @return array
     */
    protected function savingHook(Request $request, array &$validated) : array
    {
        return $this->model->getDirty();
    }

    /**
     * Run saved hook. Run this hook after full model saving.
     *
     * @param Request $request
     * @param array   $validated
     */
    protected function savedHook(Request $request, array &$validated)
    {

    }

    /**
     * Set model relations and return array of applied changes.
     *
     * @param Request $request
     * @param array   $validated
     *
     * @return array
     */
    public function setModelRelations(Request $request, array $validated)
    {
        return [];
    }

}
