## Amondar Rest Actions.
По любым вопросам образаться на e-mail - yurenery@gmail.com<br>
!!!!!! Только для Laravel >= 5.5+
## Capability table

| Laravel                            | Filter  |
| ---------------------------------- | ------- |
| 5.5.*                              |  ^1.0.*    |       
| 5.6.* - 5.7.*                      |  ^2.0.*    |       
| 5.8.*+                             |  ^3.0.*    |       
| 5.8.*+, AmondarSextant             |  ^4.1.*    |       

## Для установки 

```json
"repositories": [
    {
        "url": "https://git.attractgroup.com/amondar/RestActions.git",
        "type": "git"
    },
    {
        "url": "https://git.attractgroup.com/amondar/sextant.git",
        "type": "git"
    },
    {
        "url": "https://git.attractgroup.com/amondar/model-remember.git",
        "type": "git"
    }
]
```

```json
    "amondar/rest-actions": "^4.2",
    "amondar/sextant": "^1.1",
```

## Подключение


**Для корректной работы всех возможностей пакета рекомендуется установка пакетов**: 
1. [MYSQL Amondar's Sextant](https://git.attractgroup.com/amondar/sextant.git)..
<br>
<br>
Подключите **RestActions** трейт к контроллеру. 
Обратите внимание, что функция **public function serverFunction** обязательна для наличия в контроллере.

```php
    abstract class CoreController extends Controller
    {
        use RestActions;

        ...

        public function serverResponse()
        {
            return (new ServerResponse());
        }
    }
```

Класс **ServerResponse** на прямую совместимый с любым проектом не поставляется вместе с текущим трейтом. Ввиду особенности овтетов каждого проекта и апи. Пример реализации можно найти в этом репозитории. 
Необходимые функции для обертки данного класса:

```
    status - Функция для установки статуса в ответ.
    resource - Функция для передачи данных в ответ. Принимает на вход параметр Resource:class instance или просто массив.
    extend - Функция необходимая для расширения стандартных данных в ответе, если такие необходимы.
```

Пример использования:
```php
class CategoryController extends CoreController
{
    use IndexAction, ShowAction, StoreAction, UpdateAction, DestroyAction;

    protected $actions = [
        'index' => [
            'request'     => CategoryRequest::class,
            'view'        => 'backend.categories.index',
        ],
        'show'  => [
            'request'     => CategoryRequest::class,
        ],
        'store' => [
            'request' => CategoryRequest::class,
        ],
        'update' => [
            'request' => CategoryRequest::class,
            'parameters' => [
                'category' => Category::class
            ]
        ],
        'destroy' => [
            'request' => CategoryRequest::class,
        ]
    ];

    /**
     * UserController constructor.
     *
     * @param CategoryRepository $repository
     */
    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }
}
```

## Работа с Nemesis Filter ans Sorting

Для передачи параметров в фильтр используется ключ - **conditions**
```php
'index'   => [
    'request'    => CourseRequest::class,
    'view'       => '',
    'ajaxTransform' => false,
    'conditions' => [
        'limit' => 20,
    ],
],
```

Уникальный параметр для работы с подсчетам, можно добавить **limit=count** в запрос и получить в ответе просто количество. Удобно использовать такой подход для работы с подсчетом нотификаций, например.

## Работа без репозитория

Для использования без репозитория, укажите переменной **modelClass** имя класса модели с которым вдеется работа в текущем контроллере.

```php
protected $modelCAlss = SubCategory::calss;
```

## Работа через репозиторий

Обычно, при работе через репозиторий, трейт ищет в самом репозитории функцию с таким же названием, как и навзание функции в контроллере. Однако это не совсем удобно при некоторых реализациях.
Чтобы переопределить функцию внутри репозитория:
```php
'update' => [
    'request' => CategoryRequest::class,
    'repository' => 'someOtherUpdateFunctionName'
],
```

## Предача параметров из урла в методы репозитория

Для передачи параметров в репозиторий достатоно указать в методе репозитория необходимых параметров, однако тут есть нюансы.
Стандартный пример передачи параметров:
```php
'update' => [
    'request' => CategoryRequest::class,
    'parameters' => [
        'category' => Category::class // ключ массива в точности повторяет ключ из урла. Для правильности ввода воспользоваться php artisan route:list.
    ]
],
```
Функция в репозитории:
```php
    public function update(Repository $repository, Category $category)
```

Еслив репозитории параметр имеет другое имя класса, например, при наследовании, тоогда можно передать параметр по его имени:
```php
'update' => [
    'request' => CategoryRequest::class,
    'parameters' => [
        // ключ массива parameters в точности повторяет ключ из урла. Для правильности ввода воспользоваться php artisan route:list.
        'category' => ['class' => Category::class, 'parameter_name' => 'vocabulariable'], 
    ]
],
``` 

Функция в репозитории:
```php
    public function update(Repository $repository, Vocabulariable $vocabulariable);
```

Трейт автоматически определит необходимые параметры и передаст их в репозиторий. 
В качестве реквеста будет предеан указанный реквест, а далее параметры из массива **parameters**.
ВАЖНО: Так же можно использовать параметры для функций с многофункциональным использованием. Например:

```php
public function update(Repository $repository, Category $category, $someParam = false);
```
Допустив в примере выше необходимо передать в функцию параметр $someParams=true, но его нет в урле и роуте реквеста:
```php
'update' => [
    'request' => CategoryRequest::class,
    'parameters' => [
        'category' => Category::class, // ключ в точности повторяет ключ из урла. Для правильности ввода воспользоваться php artisan route:list.
        'someParam' => true
    ]
],
```

Так же, если ваш класс репозитория имплементирует методы **setModel** для установки модели и **model** для возврата модели, то в репозитории можно не передавать на вход,
параметр модели его импелментации. RestActions сам попробует установить модель из репозитория, если сможет найти ее в route.

## Работа с views

Некоторые функции трейта умеют загружать вьюшки. На данный момент с вьюшками работают 2(две) функции - index и show.
Для указания обработчику использовать необходимую вьюшку:
```php
'index' => [
    'request'     => CategoryRequest::class,
    'view'        => 'backend.categories.index',
],
```

Обратите внимание, что для index вьюшки будет доступен объект **$data** в котором будет выборка из базы данных.
В методе show - это переменная "snake case" от имени модели. Например, SubCategory, превращается в - $sub_category.
Так же есть функция для расширения данных всех вьюшек, напиример для построения и вывода данных меню:
```php
/**
 * TODO redeclare to add default data for all views.
 *
 * @return array
 */
protected function restDefaultData()
{
    return [];
}
```


## Нюансы и расширения
Для работы с указанным выше пакетом написаны фукнции для ограничения выборок и расширения ответов. Функции index & show.
Переопределите представленные ниже функции, чтобы изменить работу фильтра и вид ответов. Ключ **parameters** - это массив параметров для фильтра:
```php
/**
 * Return index additional parameters to filter.
 *
 * TODO Redeclare this function in controller to make request a little bit secure for example.
 *
 * @param Request $request
 * @param Router  $router
 *
 * @return array
 */
protected function getIndexFilterParameters(Request $request, Router $router)
{
    return [
        //'parameters'  => $this->getActionConditions($router)->toArray(), - Стандартное использование, достает параметры из массива $actions.
        'parameters'  => [
            'filter' => [
                'sub_category.category_id' => 1
            ]
        ],
    ];
}

/**
 * Extend index response.
 *
 * TODO redeclare this function in controller and return extended information.
 *
 * @param Request $request
 * @param Router  $router
 *
 * @return array
 */
protected function extendIndexResponse(Request $request, Router $router)
{
    return [ ];
}

// ДЛЯ SHOW ФУНКЦИИ

/**
 * Return index additional parameters to filter.
 *
 * TODO Redeclare this function in controller to make request a little bit secure for example.
 *
 * @param Request $request
 * @param Router  $router
 *
 * @return array
 */
protected function getShowFilterParameters(Request $request, Router $router)
{
    return [
        'parameters'  => $this->getActionConditions($router)->toArray(),
    ];
}

/**
 * Extend index response.
 *
 * TODO redeclare this function in controller and return extended information.
 *
 * @param Request $request
 * @param Router  $router
 *
 * @return array
 */
protected function extendShowResponse(Request $request, Router $router)
{
    return [ ];
}
```

Если в роуте Ваша модель использует не id, а любой другой параметр, переопределите стандартную функцию ларавела для моделей - **getRouteKeyName** внутри модели.
```php
/**
 * Return route key name.
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'uri';
}
```

Многие в своих роутах используют бинды, но для трейта это довольно сложно. 
Чтобы ограничить баинд модели, с которогой в данный момент ведется работа, переопределите следующую функцию и добавте в нее свои ограничения. 
Функция используется во всех функциях, где требуется работы с текущей установленной моделью и при выборке параметров из роута:
```php
 /**
 * Return model for actions.
 *
 * TODO redeclare this function and append more secure db request if needed.
 *
 * @param $id
 *
 * @return mixed
 */
protected function getModelForActions($id, Model $model, Request $request)
{
     // Check if we receive current REST model.
    if($model->is($this->restMakeModel())){
        $query =  $this->getBaseFilterQuery($request);
    }else{
        $query = $model->newQuery();
    }

    return $query->where($model->getRouteKeyName(), $id)->firstOrFail();
}
```
Для остальных запросов, типа index & show & update & delete, можно переопределить функццию **getBaseFilterQuery**.
```php
 /**
  * Return base query builder.
  *
  * @param $actionName - name of the action from actions array.
  * @param Request $request
  *                    
  * @return \Illuminate\Database\Eloquent\Builder
  */
 protected function getBaseFilterQuery(Request $request)
 {
     return $this->restMakeModel()->newQuery();
 }
```
Как можно заметить функция принимает на вход имя текущей операции, поэтому можно создавать любые бызовые запросы для любых действий включая создание, обновление и удаление.

## Наследование контроллеров
При прямом наследвоании, может так случиться, что нам необходимо добавить недостающий экшн. Для того, чтобы не повторять весь массив `actions`:
```php
    /**
     * Return extended actions.
     *
     * @param Router $router
     *
     * @return array
     */
    public function getActions(Router $router)
    {
        $actions = parent::getActions($router); // parent - Расширяемый контроллер.

        return array_merge($actions, [
            'store' => [
                'request'     => CampaignRequest::class,
                'transformer' => CampaignResource::class,
            ],
        ]);
    }
```
Метод `getActions` вызывается единоразово при выборе необходимого к вызову метода. Не забудьте подключить трейт дополняемого метода. <br>
Зачастую при снаследовании у нас меняется валидационный реквест. Поэтому была добавлена переменная - `restActionsRequest` <br>
Пример:
```php
    class UserController extends CoreController
    {
    
        use IndexAction;
    
    
        /**
         * Main request class.
         *
         * @var string
         */
        protected $restActionsRequest = UserRequest::class;
    
        /**
         * Possible actions.
         *
         * @var array
         */
        protected $actions = [
            'index'   => [
                'onlyAjax'    => true,
                'transformer' => UserResource::class,
            ],
        ];
        
        /**
         * UserController constructor.
         *
         * @param UserRepository $repository
         */
        public function __construct(UserRepository $repository)
        {
    
            $this->repository = $repository;
        }
    }
    
    class PostOwnerController extends UserController
    {
        /**
         * Main request class.
         *
         * @var string
         */
        protected $restActionsRequest = PostOwnerRequest::class;
    
    
        /**
         * PostOwnerRepository constructor.
         *
         * @param PostOwnerRepository $repository
         */
        public function __construct(PostOwnerRepository $repository)
        {
            parent::__construct($repository);
        }
    
        /**
         * Return base filter builder.
         *
         * @param $actionName
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        protected function getBaseFilterQuery(Request $request)
        {
            return $this->repository->newQuery()->hasPermission([ Permission::CAN_OWN_POST ]);
        }
    }
```
**ВАЖНО**: в примере выше, репозиторий должен быть наследником репозитория родительского контроллера.

## Расширение через создание экземпляра контроллера в другом контроллере

Для функций index & show доступна функция дополнительной настройки трансформации. Например, вам требуется создать экземпляр класс контроллера,
выбрать данные из базы, но изменить ответ таким образом, чтобы они подходил вашим требования. В таком случае вы можете воспользоваться ключем - **ajaxTransform**
```php
'index'   => [
    'request'    => CourseRequest::class,
    'view'       => '',
    'ajaxTransform' => false,
    'conditions' => [
        'limit' => 20,
    ],
],
```

```php
$response = app(CourseController::class)->index($router);

if($response instanceof Collection){
    //return response for filter and change it
    //or render spesific view with collectioned data or transform it for your self.

}

//return view
return $response;
```

Так же можно устанавливать изменяемые параметры нужного действия до вызова самого действия
```php
app(CourseController::class)
    ->setActionView('index', 'frontend.filter.sub_category')
    ->setActionRepositoryMethod('index', 'newMethodName')
    ->setActionConditions('index', [ 'limit' => 20 ])
    ->setActionParameters('index', [ 'course' => SubCategory::class ])
    ->index($router);
```

## Cache

Кеширование доступно на каждый экшн выборки - **index, show**.

```php
'index'   => [
    'request'    => CourseRequest::class,
    'cache' => [
        'time' => 120, // in secs
        'tags' => 'courses_list' // tag is not available on all cache drivers, but always required field.
    ]
],
```

По любым вопросам образаться на e-mail - yurenery@gmail.com

## Управление экшнами

Любой экшн указанный в контроллере расширяет эти параметры:
```php
[
    'request' => Request::class,
    'model' => null, 
    'view' => null,
    'conditions' => [],
    'transformer' => Resource::class,
    'ajaxTransform' => true,
    'simpleView' => false,
    'onlyAjax' => false,
    'onlyBrowser' => false,
    'cache' => [
        'time' => false,
        'tag' => null
    ],
    'protectedLimit' => self::$PROTECTED_LIMIT,
    
]   
```

Описание:

* **request** - реквест для валидации запроса
* **model** - переопределение модели для работы с запросами. **NULL** - если задан репозиторий или определена глобальная модель.
* **view** - пусть к вьюшке(по правилам blade) которую необходимо отдать на фронт в ответ на запрос не требующий json. 
* **conditions** - дополнительные статические пкараметры фильтра.
* **transformer** - Параметр класса трансформера для конкретного экшна. Если не зада, то используется обычный toArray.
* **ajaxTransform** - праметр определябщий необходимость вызова трансформера для ответа информации по данному методу. Если указано false - используется toArray().
* **simpleView** - праметр определябщий необходимость на запрос html страницы подгрузить только ее, без подключения работы с бд и фильтром. data = collection([]).
* **onlyAjax** - праметр определябщий работу исключительно при запросе json данных и с использование соответствующих headers.
* **onlyBrowser** - праметр определябщий работу исключительно при запросе html страницы.
* **cache** - массив параметров для кеширования. См. выше.
* **protectedLimit** - Параметр переорпдееляющий максимально возможный лимит для единовременного выбора фильтруемых данных. Служит защитой от подвешивания сервера при выборке огромного количества из бд без пагинации. Может быть так же установлен глобальный лимит при помощи переопределения глобальной переменной **protected static $PROTECTED_LIMIT**, по умолчания = 100 записей; 
* **sextantRestrictions** - Интеграция sextant restrictions. Для более подробной информаци прочтите соответствующий раздел документации sextant фильтра; 


 
  


