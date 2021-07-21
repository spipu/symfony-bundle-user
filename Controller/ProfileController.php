<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\UiBundle\Exception\UiException;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\ShowFactory;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Ui\PasswordForm;
use Spipu\UserBundle\Ui\ProfileForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfileController
 * @Route("/my-profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AccountController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route(
     *     "/",
     *     name="spipu_user_profile_show",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_USER')")
     * @param ShowFactory $showFactory
     * @param ProfileForm $profileForm
     * @return Response
     * @throws UiException
     */
    public function show(ShowFactory $showFactory, ProfileForm $profileForm): Response
    {
        $resource = $this->getUser();

        $manager = $showFactory->create($profileForm);
        $manager->setResource($resource);
        $manager->validate();

        return $this->render('@SpipuUser/profile/show.html.twig', ['manager' => $manager]);
    }

    /**
     * @Route(
     *     "/edit",
     *     name="spipu_user_profile_edit",
     *     methods="GET|POST"
     * )
     * @Security("is_granted('ROLE_USER')")
     * @param FormFactory $formFactory
     * @param ProfileForm $profileForm
     * @return Response
     * @throws UiException
     */
    public function edit(FormFactory $formFactory, ProfileForm $profileForm): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

    /**
     * @Route(
     *     "/password",
     *     name="spipu_user_profile_password",
     *     methods="GET|POST"
     * )
     * @Security("is_granted('ROLE_USER')")
     * @param FormFactory $formFactory
     * @param PasswordForm $passwordForm
     * @return Response
     * @throws UiException
     */
    public function password(FormFactory $formFactory, PasswordForm $passwordForm): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
