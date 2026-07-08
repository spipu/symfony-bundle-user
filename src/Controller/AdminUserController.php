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
use Spipu\UserBundle\Exception\ForbiddenRoleException;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\MailManager;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Spipu\UserBundle\Service\RoleService;
use Spipu\UserBundle\Service\UserManager;
use Spipu\UserBundle\Ui\UserForm;
use Spipu\UserBundle\Ui\UserGrid;
use Spipu\CoreBundle\Controller\AbstractController;
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
    private UserManager $userManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
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
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function updateAcl(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        RoleService $roleService,
        int $id
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var UserInterface $resource */
        $resource = $userRepository->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        $redirectResponse = $this->redirectToRoute('spipu_user_admin_show', ['id' => $resource->getId()]);

        $roleCodes = $request->request->all('acl');
        $error = $this->getAclUpdateError($resource, $roleService, $request, $roleCodes);
        if ($error !== null) {
            $this->addFlashTrans('danger', $error);
            return $redirectResponse;
        }

        try {
            $resource->setRoles($roleService->computeRolesToSave($roleCodes));
            $entityManager->flush();
            $this->addFlashTrans('success', 'spipu.ui.success.updated');
        } catch (ForbiddenRoleException) {
            $this->addFlashTrans('danger', 'spipu.user.error.acl_not_granted');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $redirectResponse;
    }

    /**
     * @param UserInterface $resource
     * @param RoleService $roleService
     * @param Request $request
     * @param string[] $roleCodes
     * @return string|null The error translation key, or null when the ACL update is allowed.
     */
    private function getAclUpdateError(
        UserInterface $resource,
        RoleService $roleService,
        Request $request,
        array $roleCodes
    ): ?string {
        if ($this->getUser()->getId() === $resource->getId()) {
            return 'spipu.user.error.yourself_acl';
        }

        if (!$resource->getActive()) {
            return 'spipu.user.error.acl_inactive';
        }

        if (!$roleService->canEditRoles($resource->getRoles())) {
            return 'spipu.user.error.acl_superior';
        }

        if (!$this->isCsrfTokenValid('update_acl_' . $resource->getId(), $request->request->get('_token'))) {
            return 'spipu.ui.error.token';
        }

        if (empty($roleCodes) || !$roleService->validateRoles($roleCodes)) {
            return 'spipu.user.error.acl_invalid';
        }

        return null;
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

    #[Route(path: '/enable/{id}', name: 'spipu_user_admin_enable', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function enable(
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
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_enable');

            return $this->redirectTo('show', $resource);
        }

        if (!$this->isCsrfTokenValid('enable_user_' . $resource->getId(), $request->request->get('_token'))) {
            $this->addFlashTrans('danger', 'spipu.ui.error.token');

            return $this->redirectTo('show', $resource);
        }

        try {
            $this->userManager->enableUser($resource);
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.enabled');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('show', $resource);
    }

    #[Route(path: '/disable/{id}', name: 'spipu_user_admin_disable', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function disable(
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
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_disable');

            return $this->redirectTo('show', $resource);
        }

        if (!$this->isCsrfTokenValid('disable_user_' . $resource->getId(), $request->request->get('_token'))) {
            $this->addFlashTrans('danger', 'spipu.ui.error.token');

            return $this->redirectTo('show', $resource);
        }

        try {
            $this->userManager->disableUser($resource);
            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.disabled');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('show', $resource);
    }

    #[Route(path: '/reset/{id}', name: 'spipu_user_admin_reset', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_USER_EDIT')]
    public function reset(
        Request $request,
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

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_reset');

            return $this->redirectTo('show', $resource);
        }

        if (!$this->isCsrfTokenValid('reset_user_' . $resource->getId(), $request->request->get('_token'))) {
            $this->addFlashTrans('danger', 'spipu.ui.error.token');

            return $this->redirectTo('show', $resource);
        }

        try {
            $mailManager->sendRecoveryEmail($resource);
            $this->addFlashTrans('success', 'spipu.user.success.reset');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('show', $resource);
    }

    private function redirectTo(string $backTo, ?UserInterface $resource = null): Response
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
}
