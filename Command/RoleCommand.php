<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use FOS\UserBundle\Util\UserManipulator;

/**
 * @author Lenar Lõhmus <lenar@city.ee>
 */
abstract class RoleCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
                new InputOption('super', null, InputOption::VALUE_NONE, 'Instead specifying role, use this to quickly add the super administrator role'),
            ));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');
        $super = (true === $input->getOption('super'));

        if (null !== $role && $super) {
            throw new \InvalidArgumentException('You can pass either the role or the --super option (but not both simultaneously).');
        }

        if (null === $role && !$super) {
            throw new \RuntimeException('Not enough arguments.');
        }

        $manipulator = $this->getContainer()->get('fos_user.util.user_manipulator');
        $this->executeRoleCommand($manipulator, $output, $username, $super, $role);
    }

    /**
     * @see Command
     *
     * @param UserManipulator $manipulator
     * @param OutputInterface $output
     * @param string          $username
     * @param boolean         $super
     * @param string          $role
     *
     * @return void
     */
    abstract protected function executeRoleCommand(UserManipulator $manipulator, OutputInterface $output, $username, $super, $role);

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (!$input->getArgument('username')) {
            $usernameQuestion = new Question('Please choose a username:');
            $usernameQuestion->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $username = $helper->ask($input, $output, $usernameQuestion);
            $input->setArgument('username', $username);
        }
        if ((true !== $input->getOption('super')) && !$input->getArgument('role')) {
            $roleQuestion = new Question('Please choose a role:');
            $roleQuestion->setValidator(function ($role) {
                if (empty($role)) {
                    throw new \Exception('Role can not be empty');
                }

                return $role;
            });
            $role = $helper->ask($input, $output, $roleQuestion);
            $input->setArgument('role', $role);
        }
    }
}
