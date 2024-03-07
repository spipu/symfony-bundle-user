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

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Spipu\UiBundle\Service\Ui\ShowFactory;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\MailManager;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Spipu\UserBundle\Service\RoleService;
use Spipu\UserBundle\Ui\UserForm;
use Spipu\UserBundle\Ui\UserGrid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AdminUserController
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 * @method UserInterface getUser()
 */
#[Route(path: '/user')]
class AdminUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'spipu_user_admin_list', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_SHOW')]
    public function index(GridFactory $gridFactory, UserGrid $userGrid): Response
    {
        $manager = $gridFactory->create($userGrid);
        $manager->setRoute('spipu_user_admin_list');
        if ($manager->validate()) {
            return $this->redirectToRoute('spipu_user_admin_list');
        }

        return $this->render('@SpipuUser/admin/index.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/edit/{id}', name: 'spipu_user_admin_edit', methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function edit(
        FormFactory $formFactory,
        UserForm $userForm,
        UserRepository $userRepository,
        int $id
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        $manager = $formFactory->create($userForm);
        $manager->setResource($resource);
        $manager->setSubmitButton('spipu.ui.action.save');
        if ($manager->validate()) {
            return $this->redirectTo('show', $resource);
        }

        return $this->render('@SpipuUser/admin/edit.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/create/', name: 'spipu_user_admin_create', methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function create(
        FormFactory $formFactory,
        UserForm $userForm,
        ModuleConfigurationInterface $moduleConfiguration,
        MailManager $mailManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $resource = $moduleConfiguration->getNewEntity();

        $manager = $formFactory->create($userForm);
        $manager->setResource($resource);
        $manager->setSubmitButton('spipu.ui.action.create');
        if ($manager->validate()) {
            try {
                $mailManager->sendRecoveryEmail($resource);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectTo('show', $resource);
        }

        return $this->render('@SpipuUser/admin/create.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/show/{id}', name: 'spipu_user_admin_show', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_SHOW')]
    public function show(
        ShowFactory $showFactory,
        UserForm $userForm,
        UserRepository $userRepository,
        RoleService $roleService,
        int $id
    ): Response {
        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        $showManager = $showFactory->create($userForm);
        $showManager->setResource($resource);
        $showManager->validate();

        return $this->render(
            '@SpipuUser/admin/show.html.twig',
            [
                'showManager' => $showManager,
                'roleService' => $roleService
            ]
        );
    }

    #[Route(path: '/update-acl/{id}', name: 'spipu_user_admin_acl', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_SHOW')]
    public function updateAcl(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        RoleService $roleService,
        int $id
    ): Response {
        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        $redirectResponse = $this->redirectToRoute('spipu_user_admin_show', ['id' => $resource->getId()]);

        $roleCodes = $request->request->all('acl');
        if (empty($roleCodes) || !is_array($roleCodes) || !$roleService->validateRoles($roleCodes)) {
            $this->addFlashTrans('danger', 'What you doing ???');
            return $redirectResponse;
        }

        try {
            $resource->setRoles($roleCodes);
            $entityManager->flush();
            $this->addFlashTrans('success', 'spipu.ui.success.updated');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $redirectResponse;
    }

    #[Route(path: '/delete/{id}', name: 'spipu_user_admin_delete', methods: 'DELETE')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_DELETE')]
    public function delete(
        Request $request,
        UserRepository $userRepository,
        int $id
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', $this->trans('spipu.user.error.yourself_delete'));

            return $this->redirectTo('list');
        }

        if (!$this->isCsrfTokenValid('delete_user_' . $resource->getId(), $request->request->get('_token'))) {
            $this->addFlashTrans('danger', 'spipu.ui.error.token');

            return $this->redirectTo('list');
        }

        try {
            $this->entityManager->remove($resource);
            $this->entityManager->flush();

            $this->addFlashTrans('success', 'spipu.ui.success.deleted');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('list');
    }

    #[Route(path: '/enable/{id}/{backTo}', name: 'spipu_user_admin_enable', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function enable(
        UserRepository $userRepository,
        int $id,
        string $backTo = 'list'
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_enable');

            return $this->redirectTo('list');
        }

        try {
            $resource->setActive(true);
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.enabled');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo($backTo, $resource);
    }

    #[Route(path: '/disable/{id}/{backTo}', name: 'spipu_user_admin_disable', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function disable(
        UserRepository $userRepository,
        int $id,
        string $backTo = 'list'
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_disable');

            return $this->redirectTo('list');
        }

        try {
            $resource->setActive(false);
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.disabled');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo($backTo, $resource);
    }

    #[Route(path: '/reset/{id}', name: 'spipu_user_admin_reset', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function reset(
        UserRepository $userRepository,
        MailManager $mailManager,
        int $id
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        try {
            $mailManager->sendRecoveryEmail($resource);
            $this->addFlashTrans('success', 'spipu.user.success.reset');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('show', $resource);
    }

    #[Route(path: '/mass-enable', name: 'spipu_user_admin_mass_enable', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function massEnable(UserRepository $userRepository, Request $request): Response
    {
        return $this->massAction($userRepository, $request, 'enable', 'spipu.user.success.mass_enabled');
    }

    #[Route(path: '/mass-disable', name: 'spipu_user_admin_mass_disable', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function massDisable(UserRepository $userRepository, Request $request): Response
    {
        return $this->massAction($userRepository, $request, 'disable', 'spipu.user.success.mass_disabled');
    }

    private function massAction(
        UserRepository $userRepository,
        Request $request,
        string $action,
        string $transLabel
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $selected = json_decode((string) $request->get('selected', ''));

        if (!is_array($selected) || count($selected) < 1) {
            $this->addFlashTrans('warning', 'spipu.ui.grid.item.at_least_one');
            return $this->redirectTo('list');
        }

        $count = 0;
        /** @var UserInterface[] $rows */
        $rows = $userRepository->findBy(['id' => $selected]);
        foreach ($rows as $row) {
            if ($this->massActionRow($row, $action)) {
                $this->entityManager->persist($row);
                $count++;
            }
        }
        $this->entityManager->flush();

        $this->addFlashTrans('success', $transLabel, ['%count' => $count]);

        return $this->redirectTo('list');
    }

    private function massActionRow(UserInterface $row, string $action): bool
    {
        if ($this->getUser()->getId() === $row->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_' . $action);
            return false;
        }

        if ($action === 'enable' && !$row->getActive()) {
            $row->setActive(true);
            return true;
        }

        if ($action === 'disable' && $row->getActive()) {
            $row->setActive(false);
            return true;
        }

        return false;
    }

    private function redirectTo(string $backTo, UserInterface $resource = null): Response
    {
        switch ($backTo) {
            case 'show':
                return $this->redirectToRoute('spipu_user_admin_show', ['id' => $resource->getId()]);

            case 'edit':
                return $this->redirectToRoute('spipu_user_admin_edit', ['id' => $resource->getId()]);

            case 'list':
            default:
                return $this->redirectToRoute('spipu_user_admin_list');
        }
    }

    private function addFlashTrans(string $type, string $message, array $params = []): void
    {
        $this->addFlash($type, $this->trans($message, $params));
    }

    private function trans(string $message, array $params = []): string
    {
        return $this->container->get('translator')->trans($message, $params);
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            'translator',
        ];
    }
}
