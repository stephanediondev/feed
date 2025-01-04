<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Member;
use App\Entity\MemberPasskey;
use App\Form\Type\MemberPasskeyType;
use App\Manager\MemberManager;
use App\Manager\MemberPasskeyManager;
use App\Model\QueryParameterFilterModel;
use App\Model\QueryParameterPageModel;
use App\Model\QueryParameterSortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\Binary\ByteBuffer;

#[Route(path: '/api', name: 'api_member_passkeys_', priority: 15)]
class MemberPasskeyController extends AbstractAppController
{
    private MemberManager $memberManager;

    private MemberPasskeyManager $memberPasskeyManager;

    public function __construct(MemberManager $memberManager, MemberPasskeyManager $memberPasskeyManager)
    {
        $this->memberManager = $memberManager;
        $this->memberPasskeyManager = $memberPasskeyManager;
    }

    /**
     * @param array<mixed> $filter
     * @param array<mixed> $page
     */
    #[Route(path: '/passkeys', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[MapQueryParameter] ?array $page, #[MapQueryParameter] ?array $filter, #[MapQueryParameter] ?string $sort): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('LIST', 'member_passkey');

        $filtersModel = new QueryParameterFilterModel($filter);

        $parameters = [];

        if ($filtersModel->getInt('member')) {
            if ($member = $this->memberManager->getOne(['id' => $filtersModel->getInt('member')])) {
                $parameters['member'] = $filtersModel->getInt('member');
                $data['entry'] = $member->toArray();
                $data['entry_entity'] = 'member';
            }
        }

        if ($filtersModel->get('type')) {
            $parameters['type'] = $filtersModel->get('type');
        }

        if ($filtersModel->getInt('days')) {
            $parameters['days'] = $filtersModel->getInt('days');
        }

        $sortModel = new QueryParameterSortModel($sort);

        if ($sortGet = $sortModel->get()) {
            $parameters['sortDirection'] = $sortGet['direction'];
            $parameters['sortField'] = $sortGet['field'];
        } else {
            $parameters['sortDirection'] = 'DESC';
            $parameters['sortField'] = 'cnt.dateModified';
        }

        $parameters['returnQueryBuilder'] = true;

        $pageModel = new QueryParameterPageModel($page);

        $pagination = $this->paginateAbstract($pageModel, $this->memberPasskeyManager->getList($parameters));

        $data['entries_entity'] = 'member_passkey';
        $data = array_merge($data, $this->jsonApi($request, $pagination, $sortModel, $filtersModel));

        $data['entries'] = [];

        foreach ($pagination as $result) {
            $entry = $result->toArray();
            $data['entries'][] = $entry;
        }

        return $this->jsonResponse($data);
    }

    #[Route(path: '/passkeys/create/options', name: 'create_options', methods: ['GET'])]
    public function createBegin(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'member_passkey');

        $status = JsonResponse::HTTP_OK;

        $member = $this->getMember();

        if ($member) {
            $rpId = $request->getHost();
            $webAuthn = new WebAuthn('WebAuthn Library', $rpId);

            $userId = bin2hex(random_bytes(16));
            $userName = $member->getEmail();
            $userDisplayName = $member->getEmail();

            $data = $webAuthn->getCreateArgs(\hex2bin($userId), $userName, $userDisplayName, 60*4, false, true);

            $challenge = $webAuthn->getChallenge()->getBinaryString();
            $member->setPasskeyChallenge($challenge);
            $this->memberManager->persist($member);


        } else {
            return $this->jsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        return new JsonResponse($data, $status);
    }

    #[Route(path: '/passkeys/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = [];

        $this->denyAccessUnlessGranted('CREATE', 'member_passkey');

        $member = $this->getMember();

        if ($member) {
            $memberPasskey = new MemberPasskey();
            $form = $this->createForm(MemberPasskeyType::class, $memberPasskey);

            $content = $this->getContent($request);

            $rpId = $request->getHost();
            $webAuthn = new WebAuthn('WebAuthn Library', $rpId);

            $clientDataJSON = base64_decode($content['clientDataJSON']);

            $attestationObject = base64_decode($content['attestationObject']);

            $challenge = new ByteBuffer($member->getPasskeyChallenge());

            // processCreate returns data to be stored for future logins.
            // in this example we store it in the php session.
            // Normaly you have to store the data in a database connected
            // with the user name.
            $userVerification = 'preferred';
            $create = $webAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, $userVerification === 'required', true, false);

            $memberPasskey->setMember($member);
            $memberPasskey->setTitle($content['title']);
            $memberPasskey->setCredentialId($content['id']);
            $memberPasskey->setPublicKey($create->credentialPublicKey);
            $this->memberPasskeyManager->persist($form->getData());

            $member->setPasskeyChallenge(null);
            $this->memberManager->persist($member);

            $data['success'] = true;

        } else {
            return $this->jsonResponse($data, JsonResponse::HTTP_FORBIDDEN);
        }

        return $this->jsonResponse($data, JsonResponse::HTTP_CREATED);
    }

    #[Route('/passkey/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $data = [];

        $passkey = $this->memberPasskeyManager->getOne(['id' => $id, 'member' => $this->getMember()]);

        if (!$passkey) {
            return $this->jsonResponse($data, JsonResponse::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $passkey);

        $data['entry'] = $passkey->toArray();
        $data['entry_entity'] = 'member_passkey';

        $this->memberPasskeyManager->remove($passkey);

        return $this->jsonResponse($data);
    }
}
