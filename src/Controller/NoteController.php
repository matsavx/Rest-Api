<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/note', name: 'note_')]
class NoteController extends AbstractController
{
    #[Route('/getAllNotes/{id}', name: 'get_all_by_user', methods: ['GET'])]
    public function getAllNoteByUser(NoteRepository $noteRepository, UserRepository $userRepository, $id): Response
    {
        try {
            $user = new User();
            $user = $userRepository->find($id);
            $notes = $noteRepository->findBy(['author' => $user]);
            $notesReturnignData = [];
            foreach ($notes as $note) {
                $noteSerialize = [
                    'name' => $note->getNoteName(),
                    'description' => $note->getNoteDescription()
                ];
                array_push($notesReturnignData, $noteSerialize);
            }
            return $this->response($notesReturnignData);
        } catch (ORMException $e) {
            return $this->response([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/{id}', name:'add', methods: ['POST'])]
    public function addNote(Request $request, UserRepository $userRepository, $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $user = $userRepository->find($id);
            $request = $this->transformJsonBody($request);
            $note = new note();
            $note->setNoteName($request->get('name'));
            $note->setNoteDescription($request->get('description'));
            $note->setAuthor($user);
            $em->persist($note);
            $em->flush();

            return $this->response(
                [
                    'status' => Response::HTTP_OK,
                    'success' => "Note added successfully",
                ]
            );
        } catch (ORMException $e) {
            return $this->response([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteNote($id, NoteRepository $noteRepository, EntityManagerInterface $em): JsonResponse
    {
        $note = $noteRepository->find($id);
        if (!$note) {
            return $this->response([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Note not found",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $em->remove($note);
        $em->flush();

        return $this->response([
            'status' => Response::HTTP_OK,
            'errors' => "Note deleted successfully",
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function updateNote($id, NoteRepository $noteRepository, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $note = $noteRepository->find($id);
        if (!$note) {
            return $this->response([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Note not found",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request = $this->transformJsonBody($request);

        $note->setNoteName($request->get('name'));
        $note->setNoteDescription($request->get('description'));

        $em->flush();

        return $this->response([
            'status' => Response::HTTP_OK,
            'errors' => "Note updated successfully",
        ]);
    }

    public function response($data, $status = Response::HTTP_OK, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $request;
        }
        $request->request->replace($data);
        return $request;
    }
}
