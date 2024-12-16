## Amondar Security Request.
По любым вопросам образаться на e-mail - yurenery@gmail.com. <br/>
Пакет предназначен для изменения и расширения работы стандартныйх Laravel реквестов в защищенных секциях сайта.<br/>
!!! ВАЖНО!!! Для работы с Laravel начиная с версии 5.6 использовать теги - "**^2.0**"
## Для установки 

```json
"repositories": [
    {
        "url": "https://git.attractgroup.com/amondar/SecurityRequest.git",
        "type": "git"
    }
]
```

```json
    "amondar/security-request": "^3.0"
```

## Подключение

Подключите **SecurityRequest** трейт к своему реквесту или создайте обертку для CoreRequest, чтобы не подключать его постоянно. 
Пример реквеста с использованием нашего трейта. Обратите внимание, используентся стандартный REST подход при котором на каждый конечный урл вешается один пермишн.<br/>

ВАЖНО: Для более общих пермишнов используйте мидлверы и обратитесь к документации ларавел.<br/>
Для проверок во всех функциях доступны переменные:
```php
    $this->action; //Массив дейсвия.
    $this->actionName; //Имя действия. Находится в ключе.
```

```php
class CategoryRequest extends FormRequest
{
    use SecurityRequest;
     
    /**
     * Actions.
     *
     * @var array
     */
    protected $actions = [
        'view' => [
            'methods'    => [ 'GET' ],
            'permission' => 'default',
        ],
        'add'  => [
            'methods'    => [ 'POST' ],
            'permission' => 'default',
        ],
        'edit' => [
            'methods'    => [ 'PUT', 'PATCH' ],
            'permission' => 'default',
        ],
        'delete' => [
            'methods'    => [ 'DELETE' ],
            'permission' => 'default',
        ]
    ];

    /**
     * Rules array.
     *
     * @return array
     */
    public function rulesArray()
    {
        $rules = [
            'uri'        => 'required|alpha_dash|unique:categories',
            'is_active'  => 'sometimes|boolean',
        ];

        $this->addTranslatableFields($rules, [
            'name' => [ 'required', 'string', 'min:1', 'max:255' ]
        ]);

        return $rules;
    }

    public function messagesArray()
    {
        return [
            'uri.required'      => trans_db(app('translations'), 'validation-categories-uri-required', 'Uri is required field.'),
            'uri.unique'        => trans_db(app('translations'), 'validation-categories-uri-unique', 'Uri with this name already exists.'),
            'name_*.required'   => trans_db(app('translations'), 'validation-categories-name-required', 'Name is required.'),
            'name_*.min'        => trans_db(app('translations'), 'validation-categories-name-min', 'Name must be at least :min characters in length.', [ ':min' => 3 ]),
            'name_*.max'        => trans_db(app('translations'), 'validation-categories-name-max', 'Name must be maximum :max characters in length.', [ ':max' => 255 ]),
        ];
    }

    /**
     * @return array
     */
    protected function postActionMessages()
    {
        return $this->messagesArray();
    }

    /**
     * Get action rules
     *
     * @return array
     */
    protected function getAction()
    {
        return [ ];
    }

    /**
     * Post action rules
     *
     * @return array
     */
    protected function postAction()
    {
        return $this->rulesArray();
    }

    /**
     * Put action rules
     *
     * @return array
     */
    protected function putAction()
    {
        $rules = $this->rulesArray();
        $category_id = $this->route('category');
        $rules['uri'] = [ 'required','alpha_dash', Rule::unique('categories', 'uri')->ignore($category_id, '_id') ];

        return $rules;
    }

    /**
     * Delete action rules
     *
     * @return array
     */
    protected function deleteAction()
    {
        return [];
    }
}
```

Как вы моджете заметить реквест стал более читабельным. 
На каждый тип запросы можно построить свой независимый массив правил и сообщений для ответа на ошибки валидации.<br/>

**ВАЖНО**: Запросы типа PUT и PATCH обрабатываются в функциях с приставкой **put** - **putAction**

## Пермишны и их возможности
Для того чтобы использовать преимущество читабельности, но опустить проверку пермишнов через Laravel Gate Facade используется имя пермишна - **default**.
Это говорит трейту, что на данном запросе не нужна проверка пермишна. Пример с пермишном:
```php
'delete' => [
    'methods'    => [ 'DELETE' ],
    'permission' => 'change-log-delete',
],
```

Данный ключ выполнит проверку - **Auth::user()->can('change-log-delete')**. В случае, если пользователь не имеет права
выполнять запрошенное дейсвие - будет возвращен ответ **403**

## Возможности расширения
Если необходимо описать на один и тот же типа запроса, например, **PUT**, несколько запросов с разным набором правил валидации : можно воспользоваться ключем - **route** 

```php
'edit'    => [
    'methods'    => [ 'PUT', 'PATCH' ],
    'route'      => 'projects/*/groups/*',
    'permission' => 'default',
],
'move-to' => [
    'methods'    => [ 'PUT', 'PATCH' ],
    'route'      => 'projects/*/groups/move',
    'permission' => 'group-task-actions',
],
```

В данном случае трейт проведет сравнение урла в реквесте при помощи стандартной функции Laravel:
```php
$this->is('projects/*/groups/move')
```

В случае успеха данной проверки будет проверен и пермишн доступа для залогиненного юзера.<br/>

ВАЖНО: при появлении таких ключей где используется формулировка с **route** Правила именования функций для таких экшнов меняются. В данной ситуации экшны будут выглядеть так:
```php
/**
* Put method rules apply.
*/
protected function putEditAction()
{
    return [];
}

/**
* Put method rules apply.
*/
protected function putMoveToAction()
{
    return [];
}
```
Для ввода дополнительных правил проверки в стандартной функции Laravel - **autorise** переопределите ее и не забудьте по умолчанию вернуть ответ parent функции.
```php
/**
 * Determine if the user is authorized to make this request.
 *
 * @return bool
 */
public function authorize()
{
    $group_id = $this->route('group');
    $user = detectUser()->user;
    if (
        ! $this->project->isTeammates([ detectUser()->user ]) ||
        ($group_id && ! $this->project->groups->contains('id_task_group', $group_id)) ||
        ($this->actionName == 'edit' && $this->task_group_status_id == 2 && $user->cannot('group-begin')) ||
        ($this->actionName == 'edit' && $this->task_group_status_id == 3 && $user->cannot('group-close'))
    ) {
        return false;
    }

    return parent::authorize();
}
```

По любым вопросам образаться на e-mail - yurenery@gmail.com
