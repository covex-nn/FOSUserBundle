<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProfileFormHandler
{
    protected $requestStack;
    protected $userManager;
    protected $form;

    public function __construct(FormInterface $form, RequestStack $requestStack, UserManagerInterface $userManager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->userManager = $userManager;
    }

    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        $request = $this->requestStack->getCurrentRequest();
        if ('POST' === $request->getMethod()) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }

            // Reloads the user to reset its username. This is needed when the
            // username or password have been changed to avoid issues with the
            // security layer.
            $this->userManager->reloadUser($user);
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $this->userManager->updateUser($user);
    }
}
