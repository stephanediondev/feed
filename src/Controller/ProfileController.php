<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Form\Type\ProfileType;
use App\Helper\JwtHelper;
use App\Manager\MemberManager;
use App\Model\ProfileModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/profile', name: 'api_profile_', priority: 20)]
class ProfileController extends AbstractAppController
{
    private MemberManager $memberManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(MemberManager $memberManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->memberManager = $memberManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $pinboard = $this->connectionManager->getOne(['type' => Connection::TYPE_PINBOARD, 'member' => $this->getMember()]);

        if ($pinboard) {
            $data['pinboard'] = $pinboard->toArray();
        }

        if ($this->getMember()) {
            $data['entry'] = $this->getMember()->toArray();
            $data['entry_entity'] = 'member';
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/connections', name: 'connections', methods: ['GET'])]
    public function connections(Request $request): JsonResponse
    {
        $data = [];

        $data['entries'] = [];

        $currentToken = null;
        if ($request->headers->get('Authorization')) {
            try {
                $payloadjwtPayloadModel = JwtHelper::getPayload(str_replace('Bearer ', '', $request->headers->get('Authorization')));
                if ($payloadjwtPayloadModel) {
                    $currentToken = $payloadjwtPayloadModel->getJwtId();
                }
            } catch (\Exception $e) {
            }
        }

        foreach ($this->connectionManager->getList(['member' => $this->getMember()])->getResult() as $connection) {
            $entry = $connection->toArray();
            if ($connection->getIp() == $request->getClientIp()) {
                $entry['currentIp'] = true;
            }
            if ($currentToken && $connection->getToken() === $currentToken) {
                $entry['currentToken'] = true;
            }
            $data['entries'][] = $entry;
        }

        if ($this->getMember()) {
            $data['entry'] = $this->getMember()->toArray();
            $data['entry_entity'] = 'member';
        }

        $data['entries_entity'] = 'connection';

        return $this->jsonResponse($data);
    }

    #[Route(path: '', name: 'update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $data = [];

        $profile = new ProfileModel();
        $form = $this->createForm(ProfileType::class, $profile);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $this->getMember()) {
            $this->getMember()->setEmail($profile->getEmail());
            if ($profile->getPassword()) {
                $this->getMember()->setPassword($this->passwordHasher->hashPassword($this->getMember(), $profile->getPassword()));
            }

            $this->memberManager->persist($this->getMember());
        } else {
            $data = $this->getFormErrors($form);
            return $this->jsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        $data['entry'] = $this->getMember()->toArray();
        $data['entry_entity'] = 'member';

        return $this->jsonResponse($data);
    }
}
