<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Member;
use App\Form\Type\ProfileType;
use App\Manager\MemberManager;
use App\Model\ProfileModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: 'api_profile_', priority: 20)]
class ProfileController extends AbstractAppController
{
    private MemberManager $memberManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(MemberManager $memberManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->memberManager = $memberManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: '/profile', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $this->getUser()]);

        if ($pinboard) {
            $data['pinboard'] = $pinboard->toArray();
        }

        $data['entry'] = $this->getUser()->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    #[Route(path: '/profile/connections', name: 'connections', methods: ['GET'])]
    public function profileConnections(Request $request): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $data['entries'] = [];

        foreach ($this->connectionManager->getList(['member' => $this->getUser()])->getResult() as $connection) {
            $entry = $connection->toArray();
            if ($connection->getIp() == $request->getClientIp()) {
                $entry['currentIp'] = true;
            }
            if ($connection->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
                $entry['currentAgent'] = true;
            }
            $data['entries'][] = $entry;
        }

        $data['entry'] = $this->getUser()->toArray();
        $data['entry_entity'] = 'member';

        $data['entries_entity'] = 'connection';

        return new JsonResponse($data);
    }

    public function profileUpdate(Request $request): JsonResponse
    {
        $data = [];

        if (false === $this->getUser() instanceof Member) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $profile = new ProfileModel();
        $form = $this->createForm(ProfileType::class, $profile);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $this->getUser()->setEmail($profile->getEmail());
            if ($profile->getPassword()) {
                $this->getUser()->setPassword($this->passwordHasher->hashPassword($this->getUser(), $profile->getPassword()));
            }

            $this->memberManager->persist($this->getUser());
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }

        $data['entry'] = $this->getUser()->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }
}
