<?php

namespace Networking\InitCmsBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GroupAdminController extends CRUDController
{
    public function handleXmlHttpRequestErrorResponse(Request $request, FormInterface $form): ?JsonResponse
    {
        return null;
    }
}