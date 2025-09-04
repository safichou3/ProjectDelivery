<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactAppController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }


}
