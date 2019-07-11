<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\UiBundle\Exception\UiException;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Spipu\UiBundle\Service\Ui\ShowFactory;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Service\MailManager;
use Spipu\UserBundle\Service\ModuleConfiguration;
use Spipu\UserBundle\Ui\UserForm;
use Spipu\UserBundle\Ui\UserGrid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminUserController
 * @Route("/user")
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class AdminUserController extends AbstractController
{
    /**
     * @Route(
     *     "/",
     *     name="spipu_user_admin_list",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_SHOW')")
     * @param GridFactory $gridFactory
     * @param UserGrid $userGrid
     * @return Response
     * @throws UiException
     */
    public function index(GridFactory $gridFactory, UserGrid $userGrid): Response
    {
        $manager = $gridFactory->create($userGrid);
        $manager->setRoute('spipu_user_admin_list');
        $manager->validate();

        return $this->render('@SpipuUser/admin/index.html.twig', ['manager' => $manager]);
    }

    /**
     * @Route(
     *     "/edit/{id}",
     *     name="spipu_user_admin_edit",
     *     methods="GET|POST"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param FormFactory $formFactory
     * @param UserForm $userForm
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @return Response
     * @throws UiException
     */
    public function edit(
        FormFactory $formFactory,
        UserForm $userForm,
        ModuleConfiguration $moduleConfiguration,
        int $id
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
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

    /**
     * @Route(
     *     "/create/",
     *     name="spipu_user_admin_create",
     *     methods="GET|POST"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param FormFactory $formFactory
     * @param UserForm $userForm
     * @param ModuleConfiguration $moduleConfiguration
     * @param MailManager $mailManager
     * @return Response
     * @throws UiException
     */
    public function create(
        FormFactory $formFactory,
        UserForm $userForm,
        ModuleConfiguration $moduleConfiguration,
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
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectTo('show', $resource);
        }

        return $this->render('@SpipuUser/admin/create.html.twig', ['manager' => $manager]);
    }

    /**
     * @Route(
     *     "/show/{id}",
     *     name="spipu_user_admin_show",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_SHOW')")
     * @param ShowFactory $showFactory
     * @param UserForm $userForm
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @return Response
     * @throws UiException
     */
    public function show(
        ShowFactory $showFactory,
        UserForm $userForm,
        ModuleConfiguration $moduleConfiguration,
        int $id
    ): Response {
        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        $manager = $showFactory->create($userForm);
        $manager->setResource($resource);
        $manager->validate();

        return $this->render('@SpipuUser/admin/show.html.twig', ['manager' => $manager]);
    }

    /**
     * @Route(
     *     "/delete/{id}",
     *     name="spipu_user_admin_delete",
     *     methods="DELETE"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_DELETE')")
     * @param Request $request
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, ModuleConfiguration $moduleConfiguration, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
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
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($resource);
            $entityManager->flush();

            $this->addFlashTrans('success', 'spipu.ui.success.deleted');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('list');
    }

    /**
     * @Route(
     *     "/enable/{id}/{backTo}",
     *     name="spipu_user_admin_enable",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @param string $backTo
     * @return Response
     */
    public function enable(ModuleConfiguration $moduleConfiguration, int $id, string $backTo = 'list'): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_enable');

            return $this->redirectTo('list');
        }

        try {
            $resource->setActive(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.enabled');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo($backTo, $resource);
    }

    /**
     * @Route(
     *     "/disable/{id}/{backTo}",
     *     name="spipu_user_admin_disable",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @param string $backTo
     * @return Response
     */
    public function disable(ModuleConfiguration $moduleConfiguration, int $id, string $backTo = 'list'): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        if ($this->getUser()->getId() === $resource->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_disable');

            return $this->redirectTo('list');
        }

        try {
            $resource->setActive(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlashTrans('success', 'spipu.user.success.disabled');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo($backTo, $resource);
    }

    /**
     * @Route(
     *     "/reset/{id}",
     *     name="spipu_user_admin_reset",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param ModuleConfiguration $moduleConfiguration
     * @param int $id
     * @param MailManager $mailManager
     * @return Response
     */
    public function reset(ModuleConfiguration $moduleConfiguration, int $id, MailManager $mailManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var GenericUser $resource */
        $resource = $moduleConfiguration->getRepository()->findOneBy(['id' => $id]);
        if (!$resource) {
            throw $this->createNotFoundException();
        }

        try {
            $mailManager->sendRecoveryEmail($resource);
            $this->addFlashTrans('success', 'spipu.user.success.reset');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectTo('show', $resource);
    }
    /**
     * @Route(
     *     "/mass-enable",
     *     name="spipu_user_admin_mass_enable",
     *     methods="POST"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param ModuleConfiguration $moduleConfiguration
     * @param Request $request
     * @return Response
     */
    public function massEnable(ModuleConfiguration $moduleConfiguration, Request $request): Response
    {
        return $this->massAction($moduleConfiguration, $request, 'enable', 'spipu.user.success.mass_enabled');
    }

    /**
     * @Route(
     *     "/mass-disable",
     *     name="spipu_user_admin_mass_disable",
     *     methods="POST"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_USER_EDIT')")
     * @param ModuleConfiguration $moduleConfiguration
     * @param Request $request
     * @return Response
     */
    public function massDisable(ModuleConfiguration $moduleConfiguration, Request $request): Response
    {
        return $this->massAction($moduleConfiguration, $request, 'disable', 'spipu.user.success.mass_disabled');
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param Request $request
     * @param string $action
     * @param string $transLabel
     * @return Response
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     */
    private function massAction(
        ModuleConfiguration $moduleConfiguration,
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

        $entityManager = $this->getDoctrine()->getManager();

        $count = 0;
        /** @var GenericUser[] $rows */
        $rows = $moduleConfiguration->getRepository()->findBy(['id' => $selected]);
        foreach ($rows as $row) {
            try {
                if ($this->massActionRow($row, $action)) {
                    $entityManager->persist($row);
                    $count++;
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }
        $entityManager->flush();

        $this->addFlashTrans('success', $transLabel, ['%count' => $count]);

        return $this->redirectTo('list');
    }

    /**
     * @param GenericUser $row
     * @param string $action
     * @return bool
     */
    private function massActionRow(GenericUser $row, string $action): bool
    {
        if ($this->getUser()->getId() === $row->getId()) {
            $this->addFlashTrans('danger', 'spipu.user.error.yourself_'.$action);
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

    /**
     * @param string $backTo
     * @param GenericUser|null $resource
     * @return Response
     */
    private function redirectTo(string $backTo, GenericUser $resource = null): Response
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

    /**
     * @param string $type
     * @param string $message
     * @param array $params
     * @return void
     */
    private function addFlashTrans(string $type, string $message, array $params = []): void
    {
        $this->addFlash($type, $this->trans($message, $params));
    }

    /**
     * @param string $message
     * @param array $params
     * @return string
     */
    private function trans(string $message, array $params = []): string
    {
        return $this->container->get('translator')->trans($message, $params);
    }

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [
            'translator',
        ];
    }
}
