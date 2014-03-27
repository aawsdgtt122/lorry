<?php

namespace Lorry\LorryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SiteController extends Controller {

	public function frontAction() {
		return $this->render('LorryBundle:Site:front.html.twig');
	}

}
