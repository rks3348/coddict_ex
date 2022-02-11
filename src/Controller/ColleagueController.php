<?php

namespace App\Controller;

use App\Entity\Colleague;
use App\Form\ColleagueType;
use App\Repository\ColleagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * @Route("/colleague")
 */
class ColleagueController extends AbstractController
{
    /**
     * @Route("/", name="colleague_index", methods={"GET"})
     */
    public function index(ColleagueRepository $colleagueRepository): Response
    {
        return $this->render('colleague/index.html.twig', [
            'colleagues' => $colleagueRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="colleague_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $errors = [];
        $colleague = new Colleague();
        $form = $this->createForm(ColleagueType::class, $colleague);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($colleague);
            if (count($errors) == 0) {

                $uploadedFile = $form['picture']->getData();

                if ($uploadedFile) {
                    $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/colleague_image';
                    $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                    $uploadedFile->move(
                        $destination,
                        $newFilename
                    );
                    $colleague->setPicture($newFilename);
                }
                $entityManager->persist($colleague);
                $entityManager->flush();
                return $this->redirectToRoute('colleague_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('colleague/new.html.twig', [
            'colleague' => $colleague,
            'form' => $form,
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/{id}", name="colleague_show", methods={"GET"})
     */
    public function show(Colleague $colleague): Response
    {
        return $this->render('colleague/show.html.twig', [
            'colleague' => $colleague,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="colleague_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Colleague $colleague, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(ColleagueType::class, $colleague);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $errors = $validator->validate($colleague);
            if (count($errors) == 0) {

                $uploadedFile = $form['picture']->getData();

                if ($uploadedFile) {
                    $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/colleague_image';
                    $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                    $uploadedFile->move(
                        $destination,
                        $newFilename
                    );
                    $colleague->setPicture($newFilename);
                }
                $entityManager->flush();
                return $this->redirectToRoute('colleague_index', [], Response::HTTP_SEE_OTHER);
            }


            // $entityManager->flush();

            // return $this->redirectToRoute('colleague_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('colleague/edit.html.twig', [
            'colleague' => $colleague,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="colleague_delete", methods={"POST"})
     */
    public function delete(Request $request, Colleague $colleague, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $colleague->getId(), $request->request->get('_token'))) {
            $entityManager->remove($colleague);
            $entityManager->flush();
        }

        return $this->redirectToRoute('colleague_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/greetings", name="colleague_greetings", methods={"GET","POST"})
     */
    public function greetings(Request $request, Colleague $colleague, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // dd($colleague);die;
        $data = [];
        $mailSent = true;
        $email = (new Email())
            ->from($colleague->getEmail())
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Greetings!')
            ->text('Welcome to the Coddict Excercise')
            ->html('<p>Welcome to the Coddict Excercise!</p>');

        try {
            $mailer->send($email);
        } catch (Exception $e) {
            // some error prevented the email sending; display an
            // error message or try to resend the message
            $errorMsg = $e->getMessage();
            $mailSent = false;
        }

        if ($mailSent) {
            $data = [
                "status" => "success",
                "msg" => "Email sent!",
                "errorMsg" => ""
            ];
        } else {
            $data = [
                "status" => "error",
                "msg" => "Somethings went wrong!",
                "errorMsg" => $errorMsg
            ];
        }
        return $this->json($data);
    }
}
