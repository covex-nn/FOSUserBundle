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
use FOS\UserBundle\Form\Model\ChangePassword;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ResettingFormHandler
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

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->form->getData()->new;
    }

    public function process(UserInterface $user)
    {
        $this->form->setData(new ChangePassword());

        $request = $this->requestStack->getCurrentRequest();
        if ('POST' === $request->getMethod()) {
            $this->form->bind($request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $user->setPlainPassword($this->getNewPassword());
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }
}
