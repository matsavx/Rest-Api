<?php

namespace App\Controller;

use App\Entity\Storage;
use App\Entity\User;
use App\Repository\StorageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/storage', name: 'storage_')]
class StorageController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param int $id
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'upload', methods: ['POST'])]
    public function uploadFile(Request $request, UserRepository $userRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        try {
            $user = new User();
            $user = $userRepository->find($id);
            $storage_directory = $this->getParameter('kernel.project_dir') . '/public/StorageFiles';
//            $storage_request = $request->files->get('file');
//            $storage_name = md5(uniqid()).".".$storage_request->guessExtension();
//            $storage = new Storage();
//            $storage->setStorageName($storage_name);
//            $storage->setStorageRealName($storage_request->getClientOriginalName());
//            $storage->setStorageSize(filesize($storage_directory . "/" . $storage_name));
            ////            $storage->setStorageSize($storage_request->getSize());
//            $storage->setStorageAuthor($userRepository->findOneBy([
//                "login"=>$user->getUserIdentifier()
//            ]));
//            $storage_request->move($storage_directory, $storage_name);
            foreach ($request->files as $storage_request) {
                $originalFilename = pathinfo($storage_request->getClientOriginalName(), PATHINFO_FILENAME);
                $storage_name = md5(uniqid()) . "." . $storage_request->guessExtension();
                $storage_request->move($storage_directory, $storage_name);

                $storage = new Storage();
                $storage->setStorageRealName($storage_request->getClientOriginalName());
                $storage->setStorageName($storage_name);
                $storage->setStorageSize($storage_request->getMaxFilesize());
                $entityManager->persist($storage);
                $entityManager->flush();
            }
            return $this->response([
                "status" => Response::HTTP_OK,
                "error" => "File upload successfully in to storage"
            ]);
        } catch (ORMException $e) {
            return $this->response([
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY,
                "error" => "Incorrectly entered data"
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param StorageRepository $storageRepository
     * @param string $storage_name
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{storage_name}', name: 'delete', methods: ['DELETE'])]
    public function deleteFile(StorageRepository $storageRepository, string $storage_name, EntityManagerInterface $entityManager): Response
    {

//        dd($storage_name);
//        dd($storage);
//        dd($this->getParameter('kernel.project_dir') . '/public/StorageFiles/' . $storage_name);
//        if(!$storage)
//            return $this->response([
//                "status"=>Response::HTTP_NOT_FOUND,
//                "error"=>"File does not exist"
//            ], Response::HTTP_NOT_FOUND);
        try {
            $storage = $storageRepository->findOneBy([
                'storage_name' => $storage_name
            ]);
            $entityManager->remove($storage);
            $entityManager->flush();
            $dir = $this->getParameter('kernel.project_dir') . '/public/StorageFiles';
            $fileDir = scandir($dir);
            foreach ($fileDir as $file) {
                if ($file == $storage_name) {
                    array_map('unlink', glob($dir . '/' . $file));
                }
            }
//            unlink($this->getParameter('kernel.project_dir') . '/public/StorageFiles/' . $storage_name);
            return $this->response([
                "status" => Response::HTTP_OK,
                "error" => "File deleted successfully"
            ]);
        } catch (ORMException $e) {
            return $this->response([
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY,
                "error" => "Incorrectly entered data"
            ]);
        }
    }

    /**
     * @param StorageRepository $storageRepository
     * @param UserRepository $userRepository
     * @param int $id
     * @return Response
     */
    #[Route('/{id}', name: 'getAllFiles', methods: ['GET'])]
    public function getAllFiles(StorageRepository $storageRepository, UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);
        $user = $userRepository->findOneBy([
            'user_login' => $user->getUserIdentifier()
        ]);
        $storages = $storageRepository->findBy([
            "storage_author" => $user
        ]);
        try {
            $data = [];
            foreach ($storages as $storage) {
                $data[] = [
                    "storage_name" => $storage->getStorageName(),
                    "storage_real_name" => $storage->getStorageRealName(),
                    "storage_size" => $storage->getStorageSize()
                ];
            }
            return $this->response($data);
        } catch (ORMException $e) {
            return $this->response([
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY,
                "error" => "Incorrectly entered data"
            ]);
        }
    }

    /**
     * @param StorageRepository $storageRepository
     * @param string $storage_name
     * @return Response
     */
    #[Route('/download/{storage_name}', name: 'download', methods: ['GET'])]
    public function download(StorageRepository $storageRepository, string $storage_name): Response
    {
        try {
            $storage = $storageRepository->findOneBy([
                'storage_name' => $storage_name
            ]);
            $server_storage = $this->getParameter('kernel.project_dir') . '/public/StorageFiles/' . $storage_name;
            $response = new BinaryFileResponse($server_storage);
            $response->headers->set('Content-Type', 'text/plain');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $storage->getStorageRealName());
            return $response;
        } catch (ORMException $e) {
            return $this->response([
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY,
                "error" => "Incorrectly entered data"
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
