<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function accessDenied(): Response
    {
        return $this->render('error/access_denied.html.twig');
    }
}
