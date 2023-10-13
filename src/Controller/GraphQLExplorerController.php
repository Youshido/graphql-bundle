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
     * @Route("/graphql/explorer")
     *
     * @return Response
     */
    public function explorerAction(): Response
    {
        $response = $this->render('feature/explorer.html.twig', [
            'graphQLUrl' => $this->generateUrl('youshido_graphql_graphql_default'),
            'tokenHeader' => 'access-token'
        ]);

        $date = DateTime::createFromFormat('U', strtotime('tomorrow'), new DateTimeZone('UTC'));
        $response->setExpires($date);
        $response->setPublic();

        return $response;
    }
}