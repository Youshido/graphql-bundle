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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GraphQLExplorerController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/graphql/explorer', name: 'youshido_graphql_explorer')]
    public function explorerAction(): Response
    {
        $response = $this->render('@GraphQLBundle/feature/explorer.html.twig', [
            'graphQLUrl' => $this->generateUrl('youshido_graphql_default'),
            'tokenHeader' => 'Authorization'
        ]);

        $date = DateTime::createFromFormat('U', strtotime('tomorrow'), new DateTimeZone('UTC'));
        $response->setExpires($date);
        $response->setPublic();

        return $response;
    }
}