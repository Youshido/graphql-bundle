<?php
/**
 * Date: 25.11.15
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\src\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use UnitEnum;
use Youshido\GraphQLBundle\src\Exception\UnableToInitializeSchemaServiceException;
use Youshido\GraphQLBundle\src\Execution\Processor;

class GraphQLController extends AbstractController
{
    public $container;

    /**
     * @Route("/graphql")
     *
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function defaultAction(): JsonResponse
    {
        try {
            $this->initializeSchemaService();
        } catch (UnableToInitializeSchemaServiceException $unableToInitializeSchemaServiceException) {
            return new JsonResponse(
                [['message' => 'Schema class ' . $this->getSchemaClass() . ' does not exist']],
                200,
                $this->getResponseHeaders()
            );
        }

        if ($this->container->get('request_stack')->getCurrentRequest()->getMethod() == 'OPTIONS') {
            return $this->createEmptyResponse();
        }

        list($queries, $isMultiQueryRequest) = $this->getPayload();

        $queryResponses = array_map(function (array $queryData) {
            return $this->executeQuery($queryData['query'], $queryData['variables']);
        }, $queries);

        $response = new JsonResponse($isMultiQueryRequest ? $queryResponses : $queryResponses[0], 200, $this->getParameter('graphql.response.headers'));

        if ($this->getParameter('graphql.response.json_pretty')) {
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        }

        return $response;
    }

    private function createEmptyResponse(): JsonResponse
    {
        return new JsonResponse([], 200, $this->getResponseHeaders());
    }

    private function executeQuery($query, $variables): array
    {
        /** @var Processor $processor */
        $processor = $this->container->get('graphql.processor');
        $processor->processPayload($query, $variables);

        return $processor->getResponseData();
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    private function getPayload(): array
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $query = $request->get('query', null);
        $variables = $request->get('variables', []);
        $isMultiQueryRequest = false;
        $queries = [];

        $variables = is_string($variables) ? json_decode($variables, true) ?: [] : [];

        $content = $request->getContent();
        if (!empty($content)) {
            if ($request->headers->has('Content-Type') && 'application/graphql' == $request->headers->get('Content-Type')) {
                $queries[] = [
                    'query' => $content,
                    'variables' => [],
                ];
            } else {
                $params = json_decode((string)$content, true);

                if ($params) {
                    // check for a list of queries
                    if (isset($params[0])) {
                        $isMultiQueryRequest = true;
                    } else {
                        $params = [$params];
                    }

                    foreach ($params as $queryParams) {
                        $query = $queryParams['query'] ?? $query;

                        if (isset($queryParams['variables'])) {
                            if (is_string($queryParams['variables'])) {
                                $variables = json_decode($queryParams['variables'], true) ?: $variables;
                            } else {
                                $variables = $queryParams['variables'];
                            }

                            $variables = is_array($variables) ? $variables : [];
                        }

                        $queries[] = [
                            'query' => $query,
                            'variables' => $variables,
                        ];
                    }
                }
            }
        } else {
            $queries[] = [
                'query' => $query,
                'variables' => $variables,
            ];
        }

        return [$queries, $isMultiQueryRequest];
    }

    /**
     * @throws Exception
     */
    private function initializeSchemaService(): void
    {
        if ($this->container->initialized('graphql.schema')) {
            return;
        }

        $this->container->set('graphql.schema', $this->makeSchemaService());
    }

    /**
     * @return ContainerAwareInterface
     *
     * @throws UnableToInitializeSchemaServiceException
     */
    private function makeSchemaService(): ContainerAwareInterface
    {
        if ($this->container->has($this->getSchemaService())) {
            return $this->container->get($this->getSchemaService());
        }

        $schemaClass = $this->getSchemaClass();
        if (!$schemaClass || !class_exists($schemaClass)) {
            throw new UnableToInitializeSchemaServiceException();
        }

        if ($this->container->has($schemaClass)) {
            return $this->container->get($schemaClass);
        }

        $schema = new $schemaClass();
        if ($schema instanceof ContainerAwareInterface) {
            $schema->setContainer($this->container);
        }

        return $schema;
    }

    /**
     * @return string
     */
    private function getSchemaClass(): string
    {
        return $this->getParameter('graphql.schema_class');
    }

    /**
     * @return string
     */
    private function getSchemaService(): string
    {
        $serviceName = $this->getParameter('graphql.schema_service');

        if (str_starts_with((string)$serviceName, '@')) {
            return substr((string)$serviceName, 1, strlen((string)$serviceName) - 1);
        }

        return $serviceName;
    }

    private function getResponseHeaders(): UnitEnum|float|int|bool|array|string|null
    {
        return $this->getParameter('graphql.response.headers');
    }
}