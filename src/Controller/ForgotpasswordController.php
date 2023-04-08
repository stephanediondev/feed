<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Connection;
use App\Form\Type\ForgotpasswordType;
use App\Helper\DeviceDetectorHelper;
use App\Helper\JwtHelper;
use App\Helper\MaxmindHelper;
use App\Manager\MemberManager;
use App\Model\JwtPayloadModel;
use App\Model\ForgotpasswordModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ForgotpasswordController extends AbstractAppController
{
    private MemberManager $memberManager;

    private TransportInterface $transport;

    private bool $maxmindEnabled;

    public function __construct(MemberManager $memberManager, TransportInterface $transport, bool $maxmindEnabled)
    {
        $this->memberManager = $memberManager;
        $this->transport = $transport;
        $this->maxmindEnabled = $maxmindEnabled;
    }

    #[Route(path: '/forgotpassword', name: 'forgotpassword', methods: ['POST'], priority: 20)]
    public function index(Request $request): JsonResponse
    {
        $data = [];

        $status = JsonResponse::HTTP_UNAUTHORIZED;

        $forgotpassword = new ForgotpasswordModel();
        $form = $this->createForm(ForgotpasswordType::class, $forgotpassword);

        $content = $this->getContent($request);
        $form->submit($content);

        if ($form->isValid() && $forgotpassword->getEmail()) {
            $member = $this->memberManager->getOne(['email' => $forgotpassword->getEmail()]);

            if ($member) {
                $identifier = JwtHelper::generateUniqueIdentifier();

                $extraFields = DeviceDetectorHelper::asArray($request);

                if ($extraFields['ip'] && '127.0.0.1' != $extraFields['ip'] && true === $this->maxmindEnabled) {
                    $data = MaxmindHelper::get($extraFields['ip']);
                    $extraFields = array_merge($extraFields, $data);
                }

                $connection = new Connection();
                $connection->setMember($member);
                $connection->setType('forgotpassword');
                $connection->setToken($identifier);
                $connection->setExtraFields($extraFields);

                $this->connectionManager->persist($connection);

                $jwtPayloadModel = new JwtPayloadModel();
                $jwtPayloadModel->setJwtId($identifier);
                $jwtPayloadModel->setAudience(strval($member->getId()));

                $tokenSigned = JwtHelper::createToken($jwtPayloadModel);

                $email = (new Email())
                ->from('hello@example.com')
                ->to($forgotpassword->getEmail())
                ->subject('Reset your pasword on feed')
                ->html('<p><a href="">'.$tokenSigned.'</a></p>');

                $this->transport->send($email);

                $status = JsonResponse::HTTP_OK;
            } else {
                $status = JsonResponse::HTTP_NOT_FOUND;
            }
        }

        return new JsonResponse($data, $status);
    }
}
