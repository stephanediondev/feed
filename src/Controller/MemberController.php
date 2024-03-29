<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Member;
use App\Form\Type\MemberType;
use App\Manager\MemberManager;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_members_', priority: 15)]
class MemberController extends AbstractAppController
{
    private MemberManager $memberManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(MemberManager $memberManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->memberManager = $memberManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param array<mixed> $page
     */
    #[Route(path: '/members', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[MapQueryParameter] ?array $page, #[MapQueryParameter] ?string $sort): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'member');

        $parameters = [];

        $parameters['returnQueryBuilder'] = true;

        $sortModel = new QueryParameterSortModel($sort);

        $pageModel = new QueryParameterPageModel($page);

        $pagination = $this->paginateAbstract($pageModel, $this->memberManager->getList($parameters));

        $data['entries_entity'] = 'member';
        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, null));

        $data['entries'] = [];

        foreach ($pagination as $result) {
            $entry = $result->toArray();
            $data['entries'][] = $entry;
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/members', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'member');

        $member = new Member();
        $form = $this->createForm(MemberType::class, $member, ['request_method' => Request::METHOD_POST]);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $member->getPlainPassword()) {
            $member->setPassword($this->passwordHasher->hashPassword($member, $member->getPlainPassword()));
            $this->memberManager->persist($member);

            $data['entry'] = $member->toArray();
            $data['entry_entity'] = 'member';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/member/{id}', name: 'read', methods: ['GET'])]
    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $member);

        $data['entry'] = $member->toArray();
        $data['entry_entity'] = 'member';

        return $this->jsonResponse($data);
    }

    #[Route('/member/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('UPDATE', $member);

        $form = $this->createForm(MemberType::class, $member, ['request_method' => Request::METHOD_PUT]);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            if ($member->getPlainPassword()) {
                $member->setPassword($this->passwordHasher->hashPassword($member, $member->getPlainPassword()));
            }
            $this->memberManager->persist($form->getData());

            $data['entry'] = $member->toArray();
            $data['entry_entity'] = 'member';
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->jsonResponse($data);
    }

    #[Route('/member/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $member);

        $data['entry'] = $member->toArray();
        $data['entry_entity'] = 'member';

        $this->memberManager->remove($member);

        return $this->jsonResponse($data);
    }
}
