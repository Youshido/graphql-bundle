<?php
/**
 * Date: 31.08.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\Controller;

use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GraphQLExplorerController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    #[Route(path: '/graphql/explorer', name: 'youshido_graphql_explorer')]
    public function explorerAction(Request $request): Response
    {
        // If there was access token in query
        $accessToken = $request->query->get('access_token') ?? '';

        $response = $this->render('@GraphQLBundle/feature/explorer.html.twig', [
            'graphQLUrl' => $this->generateUrl('youshido_graphql_default'),
            'tokenHeader' => 'Authorization',
            'accessToken' => $accessToken
        ]);

        $date = DateTime::createFromFormat('U', strtotime('tomorrow'), new DateTimeZone('UTC'));
        $response->setExpires($date);
        $response->setPublic();

        return $response;
    }
}