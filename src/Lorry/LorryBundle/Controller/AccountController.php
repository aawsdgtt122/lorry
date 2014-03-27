<?php

namespace Lorry\LorryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AccountController extends Controller {

	public function loginAction() {
		return $this->render('LorryBundle:Account:login.html.twig');
	}

	public function registerAction() {
		return $this->render('LorryBundle:Account:register.html.twig');
	}

	public function logoutAction() {
	}

	public function settingsAction() {
		return $this->render('LorryBundle:Account:settings.html.twig');
	}

}
