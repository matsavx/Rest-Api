<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    public function correctLogin(string $entered_login): bool
    {
        if (strlen($entered_login) >= 4 && strlen($entered_login) < 20) {
            if (preg_match("#^[aA-zZ_]+$#", $entered_login)) {
                return true;
            }
        }
        return false;
    }

    public function correctPassword(string $entered_password): bool
    {
        if (strlen($entered_password) >= 6 && strlen($entered_password) < 20) {
            if (preg_match("#^[aA-zZ0-9]+$#", $entered_password)) {
                return true;
            }
        }
        return false;
    }

    #[Route(name: 'user_register', methods: ['POST'])]
    public function userRegister(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $entered_login = $request->get('user_login');
        $entered_password = $request->get('password');

        if ($this->correctLogin($entered_login)) {
            if ($this->correctPassword($entered_password)) {
                if (count($userRepository->findBy(['user_login' => $entered_login])) == 0) {
                    $user = new User();
                    $user->setUserLogin($entered_login);
                    $user->setPassword($passwordHasher->hashPassword($user, $entered_password));
                    $em->persist($user);
                    $em->flush();

                    return $this->response([
                        'status' => Response::HTTP_OK,
                        'success' => 'User added successfully'
                    ]);
                } else {
                    return $this->response([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'success' => 'Entered login is already exist'
                    ]);
                }
            } else {
                return $this->response([
                'status' => Response::HTTP_BAD_REQUEST,
                'success' => 'Entered password is not valid'
                ]);
            }
        } else {
            return $this->response([
            'status' => Response::HTTP_BAD_REQUEST,
            'success' => 'Entered login is not valid'
            ]);
        }
    }

    public function response($data, $status = Response::HTTP_OK, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    private function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $request;
        }
        $request->request->replace($data);
        return $request;
    }
}
