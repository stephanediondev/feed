<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
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
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $pinboard = $this->connectionManager->getOne(['type' => 'pinboard', 'member' => $memberConnected]);

        if ($pinboard) {
            $data['pinboard'] = $pinboard->toArray();
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }

    #[Route(path: '/profile/connections', name: 'connections', methods: ['GET'])]
    public function profileConnections(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $data['entries'] = [];

        foreach ($this->connectionManager->getList(['member' => $memberConnected])->getResult() as $connection) {
            $entry = $connection->toArray();
            if ($connection->getIp() == $request->getClientIp()) {
                $entry['currentIp'] = true;
            }
            if ($connection->getAgent() == $request->server->get('HTTP_USER_AGENT')) {
                $entry['currentAgent'] = true;
            }
            $data['entries'][] = $entry;
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        $data['entries_entity'] = 'connection';

        return new JsonResponse($data);
    }

    public function profileUpdate(Request $request): JsonResponse
    {
        $data = [];
        if (!$memberConnected = $this->validateToken($request)) {
            return new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        $profile = new ProfileModel();
        $form = $this->createForm(ProfileType::class, $profile);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid()) {
            $memberConnected->setEmail($profile->getEmail());
            if ($profile->getPassword()) {
                $memberConnected->setPassword($this->passwordHasher->hashPassword($memberConnected, $profile->getPassword()));
            }

            $this->memberManager->persist($memberConnected);
        } else {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                if (method_exists($error, 'getOrigin') && method_exists($error, 'getMessage')) {
                    $data['errors'][$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }

        $data['entry'] = $memberConnected->toArray();
        $data['entry_entity'] = 'member';

        return new JsonResponse($data);
    }
}
