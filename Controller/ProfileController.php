<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UserBundle\Controller;

use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\ShowFactory;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Ui\PasswordForm;
use Spipu\UserBundle\Ui\ProfileForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/my-profile')]
class ProfileController extends AbstractController
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Route(path: '/', name: 'spipu_user_profile_show', methods: 'GET')]
    #[IsGranted('ROLE_USER')]
    public function show(ShowFactory $showFactory, ProfileForm $profileForm): Response
    {
        /** @var UserInterface $resource */
        $resource = $this->getUser();

        $manager = $showFactory->create($profileForm);
        $manager->setResource($resource);
        $manager->validate();

        return $this->render('@SpipuUser/profile/show.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/edit', name: 'spipu_user_profile_edit', methods: 'GET|POST')]
    #[IsGranted('ROLE_USER')]
    public function edit(FormFactory $formFactory, ProfileForm $profileForm): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $this->getUser();

        $manager = $formFactory->create($profileForm);
        $manager->setResource($resource);
        $manager->setSubmitButton('spipu.ui.action.save');
        if ($manager->validate()) {
            $event = new UserEvent($resource, 'edit');
            $this->eventDispatcher->dispatch($event, $event->getEventCode());

            return $this->redirectToRoute('spipu_user_profile_show', ['id' => $resource->getId()]);
        }

        return $this->render('@SpipuUser/profile/edit.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/password', name: 'spipu_user_profile_password', methods: 'GET|POST')]
    #[IsGranted('ROLE_USER')]
    public function password(FormFactory $formFactory, PasswordForm $passwordForm): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $this->getUser();

        $manager = $formFactory->create($passwordForm);
        $manager->setResource($resource);
        $manager->setSubmitButton('spipu.ui.action.update', 'key');
        if ($manager->validate()) {
            $event = new UserEvent($resource, 'password');
            $this->eventDispatcher->dispatch($event, $event->getEventCode());

            return $this->redirectToRoute('spipu_user_profile_show', ['id' => $resource->getId()]);
        }

        return $this->render('@SpipuUser/profile/password.html.twig', ['manager' => $manager]);
    }
}
