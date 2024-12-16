<?php

namespace AttractCores\PostmanDocumentation\Command;

use AttractCores\PostmanDocumentation\Postman;
use AttractCores\PostmanDocumentation\PostmanRoute;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class ExportPostmanCollection
 *
 * @package AttractCores\PostmanDocumentation
 */
class ExportPostmanCollection extends Command
{
    /** @var string */
    protected $signature = 'export:postman {--personal-access= : The bearer token should be used as personal    }';

    /** @var string */
    protected $description = 'Automatically generate a Postman collection for your API routes';

    /**
     * Router for the command
     *
     * @var \Illuminate\Routing\Router
     */
    protected Router $router;

    /**
     * File json structure
     *
     * @var array
     */
    protected array $structure;

    /**
     * Command config
     *
     * @var array
     */
    protected array $config;

    /**
     * Future filename
     *
     * @var null|string
     */
    protected ?string $filename;

    /**
     * Postman interface class.
     *
     * @var Postman
     */
    protected Postman $postman;

    /**
     * Personal bearer token or client credentials for requests without auth:api request.
     *
     * @var string|NULL
     */
    protected ?string $personalBearer;

    /**
     * ExportPostmanCollection constructor.
     *
     * @param \Illuminate\Routing\Router              $router
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @throws \ReflectionException
     */
    public function __construct(Router $router, Repository $config)
    {
        parent::__construct();

        // Initialize router and config.
        $this->router = $router;
        $this->config = $config[ 'postman' ];

        // Initialize postman interface class.
        $this->postman = new Postman($this->config);

        // Call running hook.
        Postman::startCompilation();
    }

    /**
     * @throws \ReflectionException
     */
    public function handle() : void
    {

        // Initialize command.
        $this->setFilename();
        $this->setPersonalBearerToken();

        //Initialize structure.
        $this->structure = $this->postman->getInitializedStructure($this->filename);

        /** @var PostmanRoute $route */
        foreach ( $this->getRoutes() as $route ) {

            if ( $route->isChecksPassed() && $reflectionMethod = $route->reflectionMethod() ) {
                foreach ( $route->methods() as $method ) {
                    $request = $this->postman->makeRequest(
                        $route, $method, $this->config[ 'headers' ],
                        $this->postman->getFakeFormData($this->router, $route, $method),
                        $this->personalBearer
                    );

                    if ( $this->isStructured() ) {
                        $this->postman->buildTree(
                            $this->structure,
                            $this->postman->getStructuredSteps($route),
                            $request
                        );
                    } else {
                        $this->structure[ 'item' ][] = $request;
                    }

                    break;
                }
            }
        }

        // Call finished hook.
        Postman::finished();

        Storage::disk($this->config[ 'disk' ])
               ->put(
                   $exportName = "postman/$this->filename",
                   json_encode($this->structure, JSON_UNESCAPED_SLASHES)
               );

        $this->info("Postman Collection Exported: $exportName");
    }

    /**
     * Return routes for processing;
     *
     * @return \Generator
     * @throws \ReflectionException
     */
    protected function getRoutes()
    {
        foreach ( $this->router->getRoutes() as $route ) {
            yield new PostmanRoute($route, $this->config);
        }
    }

    /**
     * Set file name for exported collection
     */
    protected function setFilename() : ExportPostmanCollection
    {
        $this->filename = str_replace(
            [ '{timestamp}', '{app}' ],
            [ date('Y_m_d_His'), Str::snake(config('app.name')) ],
            $this->config[ 'filename' ]
        );

        return $this;
    }

    /**
     * Determine that collection should be structured by namespaces.
     *
     * @return bool
     */
    protected function isStructured() : bool
    {
        return $this->config[ 'structured' ];
    }

    /**
     * Set personal bearer token for all requests.
     *
     * @return $this
     */
    protected function setPersonalBearerToken() : ExportPostmanCollection
    {
        $this->personalBearer = $this->option('personal-access');

        return $this;
    }

}
