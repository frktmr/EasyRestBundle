<?php

namespace Osm\EasyRestBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class RequestContentListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * RequestContentListener constructor.
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @param GetResponseEvent $event
     * @return bool
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $httpMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $enableContentTypeJson = $this->container->getParameter('osm_easy_rest.enable_content_type_json');

        if ($enableContentTypeJson && $request->headers->get('content-type') !== 'application/json') {
            throw new UnsupportedMediaTypeHttpException('Unsupported media type requested');
        }

        if (count($request->request->all())) {
            return false;
        }

        if (!in_array($method, $httpMethods)) {
            return false;
        }

        $content = $request->getContent();

        if ($enableContentTypeJson){
            if (empty($content)) {
                $content = '{}';
            }
        } else {
            return false;
        }

        $data = json_decode($content, true);
        if (is_array($data)) {
            $request->request = new ParameterBag($data);
        } else {
            throw new BadRequestHttpException('Unexpected JSON request');
        }

        return true;
    }
}
