<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Member;
use App\Form\Type\MemberType;
use App\Manager\MemberManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route(path: '/members', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'member');

        $member = new Member();
        $form = $this->createForm(MemberType::class, $member, ['validation_groups' => ['insert']]);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $member->getPassword()) {
            $member->setPassword($this->passwordHasher->hashPassword($member, $member->getPassword()));
            $this->memberManager->persist($member);

            $data['entry'] = $member->toArray();
            $data['entry_entity'] = 'member';
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
            return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('READ', $member);

        $data['entry'] = $member->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    #[Route('/member/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('UPDATE', $member);

        return new JsonResponse($data);
    }

    #[Route('/member/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $member = $this->memberManager->getOne(['id' => $id]);

        if (!$member) {
            return new JsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $member);

        return new JsonResponse($data);
    }
}
